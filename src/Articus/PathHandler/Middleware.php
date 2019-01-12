<?php
declare(strict_types=1);

namespace Articus\PathHandler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response as DiactorosResponse;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;
use Zend\ServiceManager\PluginManagerInterface;

class Middleware implements MiddlewareInterface, RequestHandlerInterface
{
	/**
	 * @var string
	 */
	protected $handlerName;

	/**
	 * @var MetadataProviderInterface
	 */
	protected $metadataProvider;

	/**
	 * @var PluginManagerInterface
	 */
	protected $handlerPluginManager;

	/**
	 * @var PluginManagerInterface
	 */
	protected $consumerPluginManager;

	/**
	 * @var PluginManagerInterface
	 */
	protected $attributePluginManager;

	/**
	 * @var PluginManagerInterface
	 */
	protected $producerPluginManager;

	/**
	 * @var callable
	 */
	protected $responseGenerator;

	/**
	 * Middleware constructor.
	 * @param string $handlerName
	 * @param MetadataProviderInterface $metadataProvider
	 * @param PluginManagerInterface $handlerPluginManager
	 * @param PluginManagerInterface $consumerPluginManager
	 * @param PluginManagerInterface $attributePluginManager
	 * @param PluginManagerInterface $producerPluginManager
	 * @param callable $responseGenerator
	 */
	public function __construct(
		string $handlerName,
		MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerPluginManager,
		PluginManagerInterface $consumerPluginManager,
		PluginManagerInterface $attributePluginManager,
		PluginManagerInterface $producerPluginManager,
		callable $responseGenerator
	)
	{
		$this->handlerName = $handlerName;
		$this->metadataProvider = $metadataProvider;
		$this->handlerPluginManager = $handlerPluginManager;
		$this->consumerPluginManager = $consumerPluginManager;
		$this->attributePluginManager = $attributePluginManager;
		$this->producerPluginManager = $producerPluginManager;
		$this->responseGenerator = $responseGenerator;
	}

	/**
	 * @inheritdoc
	 */
	public function process(Request $request, RequestHandlerInterface $handler): Response
	{
		return $this->handle($request);
	}

	/**
	 * @inheritdoc
	 */
	public function handle(Request $request): Response
	{
		$result = $this->generateEmptyResponse();
		try
		{
			$httpMethod = $request->getMethod();

			//Create producer
			$producer = null;
			if ($this->metadataProvider->hasProducers($this->handlerName, $httpMethod))
			{
				$accept = $this->getAcceptHeader($request);
				foreach ($this->metadataProvider->getProducers($this->handlerName, $httpMethod) as [$mediaType, $name, $options])
				{
					if ($accept->match($mediaType))
					{
						$producer = $this->producerPluginManager->build($name, $options);
						if (!($producer instanceof Producer\ProducerInterface))
						{
							throw new \LogicException(\sprintf('Invalid producer %s.', $name));
						}
						$result = $result->withHeader('Content-Type', $mediaType);
						break;
					}
				}
				if ($producer === null)
				{
					throw new Exception\NotAcceptable();
				}
			}

			try
			{
				//Parse body
				if ($this->metadataProvider->hasConsumers($this->handlerName, $httpMethod))
				{
					$consumer = null;
					$contentType = $this->getContentTypeHeader($request);
					foreach ($this->metadataProvider->getConsumers($this->handlerName, $httpMethod) as [$mediaType, $name, $options])
					{
						if ($contentType->match($mediaType))
						{
							$consumer = $this->consumerPluginManager->build($name, $options);
							if (!($consumer instanceof Consumer\ConsumerInterface))
							{
								throw new \LogicException(\sprintf('Invalid consumer %s.', $name));
							}
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
					if ($consumer === null)
					{
						throw new Exception\UnsupportedMediaType();
					}
				}
				//Calculate attributes
				foreach ($this->metadataProvider->getAttributes($this->handlerName, $httpMethod) as [$name, $options])
				{
					$attribute = $this->attributePluginManager->build($name, $options);
					if (!($attribute instanceof Attribute\AttributeInterface))
					{
						throw new \LogicException(\sprintf('Invalid attribute %s.', $name));
					}
					$request = $attribute($request);
				}
				//Handle request
				$handler = $this->handlerPluginManager->get($this->handlerName);
				$data = $this->metadataProvider->executeHandlerMethod($this->handlerName, $httpMethod, $handler, $request);
				$result = $this->populateResponseWithData($data, $result, $producer);
			}
			catch (Exception\HttpCode $e)
			{
				$result = $this->populateResponseWithException($e, $result, $producer);
			}
		}
		catch (Exception\HttpCode $e)
		{
			//TODO use default producer to prepare body?
			$result = $this->populateResponseWithException($e, $result, null);
		}

		return $result;
	}

	/**
	 * Generate empty response object.
	 * Using separate method just because there is no return type declaration for callable.
	 * @return Response
	 */
	protected function generateEmptyResponse(): Response
	{
		return ($this->responseGenerator)();
	}

	/**
	 * Makes Accept header object from PSR-7 request
	 * @param Request $request
	 * @return Accept
	 * @throws Exception\BadRequest
	 */
	protected function getAcceptHeader(Request $request): Accept
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
	 * Makes ContentType header object from PSR-7 request
	 * @param Request $request
	 * @return ContentType
	 * @throws Exception\BadRequest
	 */
	protected function getContentTypeHeader(Request $request): ContentType
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
	 * Populates response with data using producer
	 * @param mixed $data
	 * @param Response $response
	 * @param null|Producer\ProducerInterface $producer
	 * @return Response
	 */
	protected function populateResponseWithData($data, Response $response, ?Producer\ProducerInterface $producer): Response
	{
		if ($producer !== null)
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
	 * @param null|Producer\ProducerInterface $producer
	 * @return Response
	 */
	protected function populateResponseWithException(
		Exception\HttpCode $exception, Response $response, ?Producer\ProducerInterface $producer
	): Response
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