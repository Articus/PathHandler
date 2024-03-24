<?php
declare(strict_types=1);

namespace Articus\PathHandler;

use Articus\PluginManager\PluginManagerInterface;
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use function count;
use function sprintf;

class Middleware implements MiddlewareInterface, RequestHandlerInterface
{
	/**
	 * Attribute name for parsed body so consumers may return data other than null, object or array.
	 * Workaround for PSR-7 restriction on \Psr\Http\Message\ServerRequestInterface::getParsedBody return type
	 * that is enforced by most PSR-7 implementations.
	 */
	public const PARSED_BODY_ATTR_NAME = 'Articus\PathHandler\ParsedBody';

	/**
	 * @param string $handlerName
	 * @param MetadataProviderInterface $metadataProvider
	 * @param PluginManagerInterface<object> $handlerManager
	 * @param PluginManagerInterface<Consumer\ConsumerInterface> $consumerManager
	 * @param PluginManagerInterface<Attribute\AttributeInterface> $attributeManager
	 * @param PluginManagerInterface<Producer\ProducerInterface> $producerManager
	 * @param ResponseFactoryInterface $responseFactory
	 * @param array{0: string, 1: string, 2: array} $defaultProducer tuple ("media type", "producer name", "producer options")
	 */
	public function __construct(
		protected string $handlerName,
		protected MetadataProviderInterface $metadataProvider,
		/**
		 * @var PluginManagerInterface<object>
		 */
		protected PluginManagerInterface $handlerManager,
		/**
		 * @var PluginManagerInterface<Consumer\ConsumerInterface>
		 */
		protected PluginManagerInterface $consumerManager,
		/**
		 * @var PluginManagerInterface<Attribute\AttributeInterface>
		 */
		protected PluginManagerInterface $attributeManager,
		/**
		 * @var PluginManagerInterface<Producer\ProducerInterface>
		 */
		protected PluginManagerInterface $producerManager,
		protected ResponseFactoryInterface $responseFactory,
		/**
		 * Tuple ("media type", "producer name", "producer options")
		 * @var array{0: string, 1: string, 2: array}
		 */
		protected array $defaultProducer
	)
	{
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
		$result = $this->responseFactory->createResponse();
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
						$producer = ($this->producerManager)($name, $options);
						if (!($producer instanceof Producer\ProducerInterface))
						{
							throw new LogicException(sprintf('Invalid producer %s.', $name));
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
					foreach ($this->metadataProvider->getConsumers($this->handlerName, $httpMethod) as [$mediaRange, $name, $options])
					{
						if ($contentType->match($mediaRange))
						{
							$consumer = ($this->consumerManager)($name, $options);
							if (!($consumer instanceof Consumer\ConsumerInterface))
							{
								throw new LogicException(sprintf('Invalid consumer %s.', $name));
							}
							$request = $request->withAttribute(
								self::PARSED_BODY_ATTR_NAME,
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
					$attribute = ($this->attributeManager)($name, $options);
					if (!($attribute instanceof Attribute\AttributeInterface))
					{
						throw new LogicException(sprintf('Invalid attribute %s.', $name));
					}
					$request = $attribute($request);
				}
				//Handle request
				$handler = ($this->handlerManager)($this->handlerName, []);
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
			$result = $this->populateResponseWithException($e, $result, null);
		}

		return $result;
	}

	/**
	 * Makes Accept header object from PSR-7 request
	 * @param Request $request
	 * @return Header\Accept
	 * @throws Exception\BadRequest
	 */
	protected function getAcceptHeader(Request $request): Header\Accept
	{
		try
		{
			$headerValue = $request->getHeaderLine('Accept') ?: '*/*';
			return new Header\Accept($headerValue);
		}
		catch (Throwable $e)
		{
			throw new Exception\BadRequest('Invalid Accept header', $e);
		}
	}

	/**
	 * Makes ContentType header object from PSR-7 request
	 * @param Request $request
	 * @return Header\ContentType
	 * @throws Exception\BadRequest
	 */
	protected function getContentTypeHeader(Request $request): Header\ContentType
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
			return new Header\ContentType($request->getHeaderLine($headerName));
		}
		catch (Throwable $e)
		{
			throw new Exception\BadRequest('Invalid Content-Type header', $e);
		}
	}

	/**
	 * Populates response with data using producer
	 */
	protected function populateResponseWithData(mixed $data, Response $response, null|Producer\ProducerInterface $producer): Response
	{
		if ($producer === null)
		{
			[$mediaType, $name, $options] = $this->defaultProducer;
			$producer = ($this->producerManager)($name, $options);
			if (!($producer instanceof Producer\ProducerInterface))
			{
				throw new LogicException(sprintf('Invalid default producer %s.', $name));
			}
			$response = $response->withHeader('Content-Type', $mediaType);
		}

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
		return $response;
	}

	/**
	 * Populates response with data from exception using producer
	 * @param Exception\HttpCode $exception
	 * @param Response $response
	 * @param null|Producer\ProducerInterface $producer
	 * @return Response
	 */
	protected function populateResponseWithException(Exception\HttpCode $exception, Response $response, null|Producer\ProducerInterface $producer): Response
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
