<?php

namespace Articus\PathHandler;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;
use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;
use Zend\Diactoros\Response as DiactorosResponse;

/**
 * Primary service
 */
class Middleware implements MiddlewareInterface
{
	/**
	 * @var string
	 */
	protected $handlerAttr;

	/**
	 * @var RouterInterface
	 */
	protected $router;

	/**
	 * @var PluginManager
	 */
	protected $handlerPluginManager;

	/**
	 * @var CacheStorage
	 */
	protected $metadataCacheStorage;

	/**
	 * @var Consumer\PluginManager
	 */
	protected $consumerPluginManager;

	/**
	 * @var Attribute\PluginManager
	 */
	protected $attributePluginManager;

	/**
	 * @var Producer\PluginManager
	 */
	protected $producerPluginManager;

	/**
	 * Middleware constructor.
	 * @param string $handlerAttr
	 * @param RouterInterface $router
	 * @param PluginManager $handlerPluginManager
	 * @param CacheStorage $metadataCacheStorage
	 * @param Consumer\PluginManager $consumerPluginManager
	 * @param Attribute\PluginManager $attributePluginManager
	 * @param Producer\PluginManager $producerPluginManager
	 */
	public function __construct(
		$handlerAttr,
		RouterInterface $router,
		PluginManager $handlerPluginManager,
		CacheStorage $metadataCacheStorage,
		Consumer\PluginManager $consumerPluginManager,
		Attribute\PluginManager $attributePluginManager,
		Producer\PluginManager $producerPluginManager
	)
	{
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Attribute.php');
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Consumer.php');
		AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Producer.php');
		$this->handlerAttr = $handlerAttr;
		$this->router = $router;
		$this->handlerPluginManager = $handlerPluginManager;
		$this->metadataCacheStorage = $metadataCacheStorage;
		$this->consumerPluginManager = $consumerPluginManager;
		$this->attributePluginManager = $attributePluginManager;
		$this->producerPluginManager = $producerPluginManager;
	}

	/**
	 * @return string
	 */
	public function getHandlerAttr()
	{
		return $this->handlerAttr;
	}

	/**
	 * @param string $handlerAttr
	 * @return Middleware
	 */
	public function setHandlerAttr($handlerAttr)
	{
		$this->handlerAttr = $handlerAttr;
		return $this;
	}

	/**
	 * @return RouterInterface
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * @param RouterInterface $router
	 * @return self
	 */
	public function setRouter(RouterInterface $router)
	{
		$this->router = $router;
		return $this;
	}

	/**
	 * @return PluginManager
	 */
	public function getHandlerPluginManager()
	{
		return $this->handlerPluginManager;
	}

	/**
	 * @param PluginManager $handlerPluginManager
	 * @return self
	 */
	public function setHandlerPluginManager(PluginManager $handlerPluginManager)
	{
		$this->handlerPluginManager = $handlerPluginManager;
		return $this;
	}

	/**
	 * @return CacheStorage
	 */
	public function getMetadataCacheStorage()
	{
		return $this->metadataCacheStorage;
	}

	/**
	 * @param CacheStorage $metadataCacheStorage
	 * @return self
	 */
	public function setMetadataCacheStorage(CacheStorage $metadataCacheStorage)
	{
		$this->metadataCacheStorage = $metadataCacheStorage;
		return $this;
	}

	/**
	 * @return Consumer\PluginManager
	 */
	public function getConsumerPluginManager()
	{
		return $this->consumerPluginManager;
	}

	/**
	 * @param Consumer\PluginManager $consumerPluginManager
	 * @return self
	 */
	public function setConsumerPluginManager(Consumer\PluginManager $consumerPluginManager)
	{
		$this->consumerPluginManager = $consumerPluginManager;
		return $this;
	}

	/**
	 * @return Attribute\PluginManager
	 */
	public function getAttributePluginManager()
	{
		return $this->attributePluginManager;
	}

	/**
	 * @param Attribute\PluginManager $attributePluginManager
	 * @return self
	 */
	public function setAttributePluginManager(Attribute\PluginManager $attributePluginManager)
	{
		$this->attributePluginManager = $attributePluginManager;
		return $this;
	}

	/**
	 * @return Producer\PluginManager
	 */
	public function getProducerPluginManager()
	{
		return $this->producerPluginManager;
	}

	/**
	 * @param Producer\PluginManager $producerPluginManager
	 * @return self
	 */
	public function setProducerPluginManager(Producer\PluginManager$producerPluginManager)
	{
		$this->producerPluginManager = $producerPluginManager;
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function process(Request $request, DelegateInterface $delegate)
	{
		$response = new DiactorosResponse();
		return $this($request, $response);
	}

	/**
	 * The method was left intact to remain compatibility with Zend\Stratigility\MiddlewareInterface
	 * @param Request $request
	 * @param Response $response
	 * @param callable|null $next
	 * @return Response
	 */
	public function __invoke(Request $request, Response $response, callable $next = null)
	{
		try
		{
			//Update request with routing data
			$request = $this->routeRequest($request);

			//Retrieve handler object
			$handler = $this->getHandler($request);
			$handlerMethod = $this->getHandlerMethod($handler, $request->getMethod());
			if ($handlerMethod === null)
			{
				//TODO add allowed methods
				throw new Exception\MethodNotAllowed();
			}
			$metadata = $this->getHandlerMethodMetadata($handler, $handlerMethod);

			//Create producer
			$producer = null;
			if (!$metadata->producers->isEmpty())
			{
				$accept = $this->getAcceptHeader($request);
				foreach ($this->getProducers($metadata) as $mediaType => list($name, $options))
				{
					if ($accept->match($mediaType))
					{
						$producer = $this->producerPluginManager->get($name, $options);
						$response = $response->withHeader('Content-Type', $mediaType);
						break;
					}
				}
				if (!($producer instanceof Producer\ProducerInterface))
				{
					throw new Exception\NotAcceptable();
				}
			}

			try
			{
				//Parse body
				$consumer = null;
				if (!$metadata->consumers->isEmpty())
				{
					$contentType = $this->getContentTypeHeader($request);
					foreach ($this->getConsumers($metadata) as $mediaType => list($name, $options))
					{
						if ($contentType->match($mediaType))
						{
							$consumer = $this->consumerPluginManager->get($name, $options);
							$request = $request->withParsedBody(
								$consumer->parse(
									$request->getBody(),
									$request->getParsedBody(),
									$contentType->getMediaType(),
									$contentType->getParameters()
								)
							);
							break;
						}
					}
					if (!($consumer instanceof Consumer\ConsumerInterface))
					{
						throw new Exception\UnsupportedMediaType();
					}
				}
				//Calculate attributes
				foreach ($this->getAttributes($metadata) as list($name, $options))
				{
					$attribute = $this->attributePluginManager->get($name, $options);
					$request = $attribute($request);
				}
				//Handle request
				$data = $handler->{$handlerMethod}($request);
				$response = $this->populateResponseWithData($data, $response, $producer);
			}
			catch (Exception\HttpCode $e)
			{
				$response = $this->populateResponseWithException($e, $response, $producer);
			}
		}
		catch (Exception\HttpCode $e)
		{
			//TODO use default producer to prepare body?
			$response = $this->populateResponseWithException($e, $response, null);
		}

		return (is_callable($next) ? $next($request, $response) : $response);
	}

	/**
	 * Updates request with routing results
	 * @param Request $request
	 * @return Request
	 * @throws Exception\NotFound
	 */
	protected function routeRequest(Request $request)
	{
		$routeResult = $this->router->match($request);
		if ($routeResult->isFailure())
		{
			throw new Exception\NotFound();
		}
		foreach ($routeResult->getMatchedParams() as $paramName => $paramValue)
		{
			$request = $request->withAttribute($paramName, $paramValue);
		}
		$request = $request->withAttribute(RouteResult::class, $routeResult);
		return $request;
	}

	/**
	 * Returns handler object required by request
	 * @param Request $request
	 * @return object
	 */
	protected function getHandler(Request $request)
	{
		$handler = $request->getAttribute($this->handlerAttr);
		switch (true)
		{
			case empty($handler):
				throw new \LogicException(sprintf('Path handler is not configured for "%s".', $request->getUri()));
			case is_object($handler):
				//Allow to pass handler object directly
				break;
			case (is_string($handler) && $this->handlerPluginManager->has($handler)):
				$handler = $this->handlerPluginManager->get($handler);
				break;
			default:
				throw new \LogicException(sprintf('Invalid path handler for "%s".', $request->getUri()));
		}
		return $handler;
	}

	/**
	 * Returns handler method name that should be used to handle request with specified HTTP-method
	 * @param $handler
	 * @param string $httpMethod
	 * @return string|null
	 */
	protected function getHandlerMethod($handler, $httpMethod)
	{
		$result = null;
		$operation = $this->getOperationData($httpMethod);
		if ($operation !== null)
		{
			list($handlerInterface, $handlerMethod) = $operation;
			if ($handler instanceof $handlerInterface)
			{
				$result = $handlerMethod;
			}
		}
		return $result;
	}

	/**
	 * Returns information about operation that should handle specified http method call
	 * @param string $httpMethod
	 * @return string[]|null - tuple (<operation interface name>, <operation method name>)
	 */
	protected function getOperationData($httpMethod)
	{
		switch ($httpMethod)
		{
			case 'GET':
				return [Operation\GetInterface::class, Operation\MethodEnum::GET];
			case 'POST':
				return [Operation\PostInterface::class, Operation\MethodEnum::POST];
			case 'PATCH':
				return [Operation\PatchInterface::class, Operation\MethodEnum::PATCH];
			case 'DELETE':
				return [Operation\DeleteInterface::class, Operation\MethodEnum::DELETE];
			case 'PUT':
				return [Operation\PutInterface::class, Operation\MethodEnum::PUT];
			default:
				return null;
		}
	}

	/**
	 * Returns metadata for handler method
	 * @param $handler
	 * @param string $handlerMethod
	 * @return Metadata
	 */
	protected function getHandlerMethodMetadata($handler, $handlerMethod)
	{
		$handlerClassName = get_class($handler);
		if ($handlerClassName === false)
		{
			throw new \LogicException(
				sprintf('Invalid handler type: %s.', gettype($handler))
			);
		}
		$metadataCacheKey = $this->getHandlerMetadataCacheKey($handlerClassName);
		$metadata = $this->metadataCacheStorage->getItem($metadataCacheKey);
		if ($metadata === null)
		{
			$metadata = $this->readHandlerMetadata($handlerClassName);
			$this->metadataCacheStorage->addItem($metadataCacheKey, $metadata);
		}
		if (empty($metadata[$handlerMethod]))
		{
			throw new \LogicException(
				sprintf('No metadata for method %s in handler %s.', $handlerMethod, $handlerClassName)
			);
		}
		if (!($metadata[$handlerMethod] instanceof Metadata))
		{
			throw new \LogicException(
				sprintf('Invalid metadata for method %s in handler %s.', $handlerMethod, $handlerClassName)
			);
		}
		return $metadata[$handlerMethod];
	}

	/**
	 * Return key for cache adapter to store handler metadata
	 * @param string $handlerClassName
	 * @return string
	 */
	protected function getHandlerMetadataCacheKey($handlerClassName)
	{
		return str_replace('\\', '_', $handlerClassName);
	}

	/**
	 * Reads handler metadata from annotations
	 * @param string $handlerClassName
	 * @return Metadata[]
	 */
	protected function readHandlerMetadata($handlerClassName)
	{
		$result = [];

		$reflection = new \ReflectionClass($handlerClassName);
		$reader = new AnnotationReader();

		$commonMetadata = new Metadata();
		$commonMetadata->addAnnotations($reader->getClassAnnotations($reflection));

		foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
		{
			if ($this->isOperationMethod($method->getName()))
			{
				$metadata = clone $commonMetadata;
				$metadata->addAnnotations($reader->getMethodAnnotations($method));
				$result[$method->getName()] = $metadata;
			}
		}

		return $result;
	}

	/**
	 * Checks if specified method is used for operation handling
	 * @param string $methodName
	 * @return bool
	 */
	protected function isOperationMethod($methodName)
	{
		switch ($methodName)
		{
			case Operation\MethodEnum::GET:
			case Operation\MethodEnum::POST:
			case Operation\MethodEnum::PATCH:
			case Operation\MethodEnum::DELETE:
			case Operation\MethodEnum::PUT:
				return true;
			default:
				return false;
		}
	}

	/**
	 * Makes Accept header object from PSR-7 request
	 * @param Request $request
	 * @return Accept
	 * @throws Exception\BadRequest
	 */
	protected function getAcceptHeader(Request $request)
	{
		$headerName = 'Accept';
		$headerLine = '*/*';
		if ($request->hasHeader($headerName))
		{
			$headerLine = $request->getHeaderLine($headerName);
		}
		try
		{
			return Accept::fromString($headerName . ': ' . $headerLine);
		}
		catch (\Exception $e)
		{
			throw new Exception\BadRequest('Invalid Accept header', $e);
		}
	}

	/**
	 * Allows to iterate through producers in metadata in right order
	 * @param Metadata $metadata
	 * @return \Generator
	 */
	protected function getProducers(Metadata $metadata)
	{
		foreach ($metadata->producers as $producer)
		{
			yield $producer->mediaType => [$producer->name, $producer->options];
		}
	}

	/**
	 * Makes ContentType header object from PSR-7 request
	 * @param Request $request
	 * @return ContentType
	 * @throws Exception\BadRequest
	 */
	protected function getContentTypeHeader(Request $request)
	{
		$headerName = 'Content-Type';
		if (!$request->hasHeader($headerName))
		{
			throw new Exception\BadRequest('Content-Type header should be declared');
		}
		if (count($request->getHeader($headerName)) > 1)
		{
			throw new Exception\BadRequest('Multiple Content-Type headers are not allowed');
		}
		try
		{
			return ContentType::fromString($headerName . ': ' . $request->getHeaderLine($headerName));
		}
		catch (\Exception $e)
		{
			throw new Exception\BadRequest('Invalid Content-Type header', $e);
		}
	}

	/**
	 * Allows to iterate through attributes in metadata in right order
	 * @param Metadata $metadata
	 * @return \Generator
	 */
	protected function getConsumers(Metadata $metadata)
	{
		foreach ($metadata->consumers as $consumer)
		{
			yield $consumer->mediaType => [$consumer->name, $consumer->options];
		}
	}

	/**
	 * Allows to iterate through attributes in metadata in right order
	 * @param Metadata $metadata
	 * @return \Generator
	 */
	protected function getAttributes(Metadata $metadata)
	{
		foreach ($metadata->attributes as $attribute)
		{
			yield [$attribute->name, $attribute->options];
		}
	}

	/**
	 * Populates response with data using producer
	 * @param $data
	 * @param Response $response
	 * @param $producer
	 * @return Response
	 */
	protected function populateResponseWithData($data, Response $response, $producer)
	{
		if ($producer instanceof Producer\ProducerInterface)
		{
			$stream = $producer->assemble($data);
			if ($stream instanceof StreamInterface)
			{
				$response = $response->withBody($stream);
			}
			if ($producer instanceof Producer\HeaderInterface)
			{
				foreach ($producer->assembleHeaders($data) as $name => $value)
				{
					$response = $response->withHeader($name, $value);
				}
			}
		}
		return $response;
	}

	/**
	 * Populates response with data from exception using producer
	 * @param Exception\HttpCode $exception
	 * @param Response $response
	 * @param $producer
	 * @return Response
	 */
	protected function populateResponseWithException(Exception\HttpCode $exception, Response $response, $producer)
	{
		$response = $this->populateResponseWithData($exception->getPayload(), $response, $producer);
		if ($exception instanceof Exception\HeaderInterface)
		{
			foreach ($exception->getHeaders() as $name => $value)
			{
				$response = $response->withHeader($name, $value);
			}
		}
		$response = $response->withStatus($exception->getCode(), $exception->getMessage());
		return $response;
	}
}
