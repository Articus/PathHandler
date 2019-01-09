<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler;

use Articus\PathHandler as PH;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamInterface;
use Zend\ServiceManager\PluginManagerInterface;

class MiddlewareSpec extends ObjectBehavior
{
	public function getMatchers(): array
	{
		return [
			'beHttpResponse' => function($subject, ?int $code, ?string $reason, ?array $requiredHeaders, ?StreamInterface $body)
			{
				if (!($subject instanceof Response))
				{
					throw new FailureException(\sprintf(
						'Invalid response: expecting %s, not %s.',
						Response::class,
						\is_object($subject) ? \get_class($subject) : \gettype($subject)
					));
				}
				if (($code !== null) && ($subject->getStatusCode() !== $code))
				{
					throw new FailureException(\sprintf(
						'Invalid response status code: expecting %s, not %s.', $code, $subject->getStatusCode()
					));
				}
				if (($reason !== null) && ($subject->getReasonPhrase() !== $reason))
				{
					throw new FailureException(\sprintf(
						'Invalid response reason phrase: expecting %s, not %s.', $reason, $subject->getReasonPhrase()
					));
				}
				if ($requiredHeaders !== null)
				{
					$responseHeaders = $subject->getHeaders();
					$missingHeaders = \array_diff_key($requiredHeaders, $responseHeaders);
					if (!empty($missingHeaders))
					{
						throw new FailureException(\sprintf(
							'Invalid response headers: missing %s.', \implode(', ', \array_keys($missingHeaders))
						));
					}
					foreach ($requiredHeaders as $name => $values)
					{
						$missingValues = \array_diff($values, $responseHeaders[$name]);
						if (!empty($missingValues))
						{
							throw new FailureException(\sprintf(
								'Invalid response header %s: missing values %s.', $name, \implode(', ', \array_keys($missingHeaders))
							));
						}
					}
				}
				if (($body !== null) && (($subject->getBody()) !== $body))
				{
					throw new FailureException('Invalid response body');
				}
				return true;
			}
		];
	}

	public function it_generates_empty_successful_response_if_there_is_no_consumers_attributes_producers(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request,
		$handler
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$emptyGenerator = function()
		{
			yield from [];
		};
		$handlerData = ['test' => 123];
		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(200, null, null, null);
	}

	public function it_generates_successful_response_with_first_producer_if_there_is_no_accept(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$emptyGenerator = function()
		{
			yield from [];
		};
		$handlerData = ['test' => 123];
		$producerMetadata = [
			['test_1/mime', 'test_producer_1', ['test_option_1' => 123]],
			['test_2/mime', 'test_producer_2', ['test_option_2' => 123]],
		];
		$producerMetadataGenerator = function () use ($producerMetadata)
		{
			yield from $producerMetadata;
		};
		$responseHeaders = [
			'Content-Type' => [$producerMetadata[0][0]],
		];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->build($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Accept')->shouldBeCalledOnce()->willReturn(false);

		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(200, null, $responseHeaders, $responseBody);
	}

	public function it_generates_bad_request_response_on_invalid_accept_if_there_is_producer(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';

		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Accept')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn("invalid\rvalue");

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(400, 'Invalid Accept header', null, null);
	}

	public function it_generates_not_acceptable_response_if_producer_does_not_match_accept(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$producerMetadata = [
			['test_1/mime', 'test_producer_1', ['test_option_1' => 123]],
			['test_2/mime', 'test_producer_2', ['test_option_2' => 123]],
		];
		$producerMetadataGenerator = function () use ($producerMetadata)
		{
			yield from $producerMetadata;
		};

		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Accept')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn('test/mime');

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(406, 'Not acceptable', null, null);
	}

	public function it_throws_on_invalid_producer_if_it_matches_accept(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$producerMetadata = [
			['test_1/mime', 'test_producer_1', ['test_option_1' => 123]],
		];
		$producerMetadataGenerator = function () use ($producerMetadata)
		{
			yield from $producerMetadata;
		};

		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);

		$producerManager->build($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn(null);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Accept')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[0][0]);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->shouldThrow(\LogicException::class)->during('handle', [$request]);
	}

	public function it_generates_successful_response_with_first_producer_that_matches_accept(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$emptyGenerator = function ()
		{
			yield from [];
		};
		$handlerData = ['test' => 123];
		$producerMetadata = [
			['test_1/mime', 'test_producer_1', ['test_option_1' => 123]],
			['test_2/mime', 'test_producer_2', ['test_option_2' => 123]],
			['test_3/mime', 'test_producer_3', ['test_option_3' => 123]],
		];
		$producerMetadataGenerator = function () use ($producerMetadata)
		{
			yield from $producerMetadata;
		};
		$responseHeaders = [
			'Content-Type' => [$producerMetadata[1][0]],
		];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->build($producerMetadata[1][1], $producerMetadata[1][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Accept')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[1][0]);

		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(200, null, $responseHeaders, $responseBody);
	}

	public function it_generates_successful_response_with_extra_headers_if_producer_provides_them(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$emptyGenerator = function ()
		{
			yield from [];
		};
		$handlerData = ['test' => 123];
		$producerMetadata = [
			['test_1/mime', 'test_producer_1', ['test_option_1' => 123]],
		];
		$producerMetadataGenerator = function () use ($producerMetadata)
		{
			yield from $producerMetadata;
		};
		$producerHeaders = [
			'test_1' => 'val_1',
			'test_2' => 'val_2',
		];
		$producerHeaderGenerator = function () use ($producerHeaders)
		{
			yield from $producerHeaders;
		};
		$responseHeaders = [
			'Content-Type' => [$producerMetadata[0][0]],
			'test_1' => [$producerHeaders['test_1']],
			'test_2' => [$producerHeaders['test_2']],
		];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->build($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Accept')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[0][0]);

		$producer->implement(PH\Producer\HeaderInterface::class);
		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);
		$producer->assembleHeaders($handlerData)->shouldBeCalledOnce()->will($producerHeaderGenerator);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(200, null, $responseHeaders, $responseBody);
	}

	public function it_generates_response_with_payload_on_http_code_exception_if_there_is_producer(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		PH\Exception\HttpCode $exception,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$emptyGenerator = function ()
		{
			yield from [];
		};
		$exceptionCode = 123;
		$exceptionReason = 'Test reason';
		$exceptionPayload = ['test' => 123];
		$producerMetadata = [
			['test_1/mime', 'test_producer_1', ['test_option_1' => 123]],
		];
		$producerMetadataGenerator = function () use ($producerMetadata)
		{
			yield from $producerMetadata;
		};
		$responseHeaders = [
			'Content-Type' => [$producerMetadata[0][0]],
		];

		$exception->beConstructedWith([$exceptionCode, $exceptionReason, $exceptionPayload]);
		$exception->getPayload()->shouldBeCalledOnce()->willReturn($exceptionPayload);

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willThrow($exception->getWrappedObject());

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->build($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Accept')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[0][0]);

		$producer->assemble($exceptionPayload)->shouldBeCalledOnce()->willReturn($responseBody);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse($exceptionCode, $exceptionReason, $responseHeaders, $responseBody);
	}

	public function it_generates_response_with_extra_headers_on_http_code_exception_if_it_provides_them_and_there_is_producer(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		PH\Exception\HttpCode $exception,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$emptyGenerator = function ()
		{
			yield from [];
		};
		$exceptionCode = 123;
		$exceptionReason = 'Test reason';
		$exceptionPayload = ['test' => 123];
		$exceptionHeaders = [
			'test_1' => 'val_1',
			'test_2' => 'val_2',
		];
		$exceptionHeaderGenerator = function() use ($exceptionHeaders)
		{
			yield from $exceptionHeaders;
		};
		$producerMetadata = [
			['test_1/mime', 'test_producer_1', ['test_option_1' => 123]],
		];
		$producerMetadataGenerator = function () use ($producerMetadata)
		{
			yield from $producerMetadata;
		};
		$responseHeaders = [
			'Content-Type' => [$producerMetadata[0][0]],
			'test_1' => [$exceptionHeaders['test_1']],
			'test_2' => [$exceptionHeaders['test_2']],
		];

		$exception->beConstructedWith([$exceptionCode, $exceptionReason, $exceptionPayload]);
		$exception->implement(PH\Exception\HeaderInterface::class);
		$exception->getPayload()->shouldBeCalledOnce()->willReturn($exceptionPayload);
		$exception->getHeaders()->shouldBeCalledOnce()->will($exceptionHeaderGenerator);

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willThrow($exception->getWrappedObject());

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->build($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Accept')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[0][0]);

		$producer->assemble($exceptionPayload)->shouldBeCalledOnce()->willReturn($responseBody);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse($exceptionCode, $exceptionReason, $responseHeaders, $responseBody);
	}


	public function it_generates_bad_request_if_there_is_consumer_and_no_content_type(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(false);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(400, 'Content-Type header should be declared', null, null);
	}

	public function it_generates_bad_request_response_if_there_is_consumer_and_several_content_types(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn(['test_1/mime', 'test_2/mime']);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(400, 'Multiple Content-Type headers are not allowed', null, null);
	}

	public function it_generates_bad_request_response_on_invalid_content_type_if_there_is_consumer(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn(["invalid\rvalue"]);
		$request->getHeaderLine('Content-Type')->shouldBeCalledOnce()->willReturn("invalid\rvalue");

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(400, 'Invalid Content-Type header', null, null);
	}

	public function it_generates_unsupported_media_type_response_if_consumer_does_not_match_content_type(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$consumerMetadata = [
			['test_1/mime', 'test_consumer_1', ['test_option_1' => 123]],
			['test_2/mime', 'test_consumer_2', ['test_option_2' => 123]],
		];
		$consumerMetadataGenerator = function () use ($consumerMetadata)
		{
			yield from $consumerMetadata;
		};

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($consumerMetadataGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn(['test/mime']);
		$request->getHeaderLine('Content-Type')->shouldBeCalledOnce()->willReturn('test/mime');

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(415, 'Unsupported media type', null, null);
	}

	public function it_throws_on_invalid_consumer_if_it_matches_content_type(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$consumerMetadata = [
			['test_1/mime', 'test_consumer_1', ['test_option_1' => 123]],
		];
		$consumerMetadataGenerator = function () use ($consumerMetadata)
		{
			yield from $consumerMetadata;
		};

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($consumerMetadataGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$consumerManager->build($consumerMetadata[0][1], $consumerMetadata[0][2])->shouldBeCalledOnce()->willReturn(null);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn([$consumerMetadata[0][0]]);
		$request->getHeaderLine('Content-Type')->shouldBeCalledOnce()->willReturn($consumerMetadata[0][0]);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->shouldThrow(\LogicException::class)->during('handle', [$request]);
	}

	public function it_parses_body_with_first_consumer_that_matches_content_type(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request,
		StreamInterface $requestBody,
		$handler,
		PH\Consumer\ConsumerInterface $consumer
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$requestData = ['request_test' => 123];
		$handlerData = ['test' => 123];
		$emptyGenerator = function ()
		{
			yield from [];
		};
		$consumerMetadata = [
			['test_1/mime', 'test_consumer_1', ['test_option_1' => 123]],
			['test_2/mime', 'test_consumer_2', ['test_option_2' => 123]],
			['test_3/mime', 'test_consumer_3', ['test_option_3' => 123]],
		];
		$consumerMetadataGenerator = function () use ($consumerMetadata)
		{
			yield from $consumerMetadata;
		};
		$consumerData = ['consumer_test' => 123];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($consumerMetadataGenerator);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$consumerManager->build($consumerMetadata[1][1], $consumerMetadata[1][2])->shouldBeCalledOnce()->willReturn($consumer);
		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn([$consumerMetadata[1][0]]);
		$request->getHeaderLine('Content-Type')->shouldBeCalledOnce()->willReturn($consumerMetadata[1][0]);
		$request->getBody()->shouldBeCalledOnce()->willReturn($requestBody);
		$request->getParsedBody()->shouldBeCalledOnce()->willReturn($requestData);
		$request->withParsedBody($consumerData)->shouldBeCalledOnce()->willReturn($request);

		$consumer->parse($requestBody, $requestData, $consumerMetadata[1][0], [])->shouldBeCalledOnce()->willReturn($consumerData);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(200, null, null, null);
	}

	public function it_throws_on_invalid_attribute(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$attributeMetadata = [
			['test_attribute_1', ['test_option_1' => 123]],
		];
		$attributeMetadataGenerator = function () use ($attributeMetadata)
		{
			yield from $attributeMetadata;
		};

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($attributeMetadataGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$attributeManager->build($attributeMetadata[0][0], $attributeMetadata[0][1])->shouldBeCalledOnce()->willReturn(null);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->shouldThrow(\LogicException::class)->during('handle', [$request]);
	}

	public function it_attributes_request_with_all_attributes_in_order(
		PH\MetadataProviderInterface $metadataProvider,
		PluginManagerInterface $handlerManager,
		PluginManagerInterface $consumerManager,
		PluginManagerInterface $attributeManager,
		PluginManagerInterface $producerManager,
		Request $request,
		Request $request1,
		Request $request2,
		Request $request3,
		$handler,
		PH\Attribute\AttributeInterface $attribute1,
		PH\Attribute\AttributeInterface $attribute2,
		PH\Attribute\AttributeInterface $attribute3
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$handlerData = ['test' => 123];
		$attributeMetadata = [
			['test_attribute_1', ['test_option_1' => 123]],
			['test_attribute_2', ['test_option_2' => 123]],
			['test_attribute_3', ['test_option_3' => 123]],
		];
		$attributeMetadataGenerator = function () use ($attributeMetadata)
		{
			yield from $attributeMetadata;
		};

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($attributeMetadataGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request3)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->get($handlerName)->shouldBeCalledOnce()->willReturn($handler);
		$attributeManager->build($attributeMetadata[0][0], $attributeMetadata[0][1])->shouldBeCalledOnce()->willReturn($attribute1);
		$attributeManager->build($attributeMetadata[1][0], $attributeMetadata[1][1])->shouldBeCalledOnce()->willReturn($attribute2);
		$attributeManager->build($attributeMetadata[2][0], $attributeMetadata[2][1])->shouldBeCalledOnce()->willReturn($attribute3);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);

		$attribute1->__invoke($request)->shouldBeCalledOnce()->willReturn($request1);
		$attribute2->__invoke($request1)->shouldBeCalledOnce()->willReturn($request2);
		$attribute3->__invoke($request2)->shouldBeCalledOnce()->willReturn($request3);

		$this->beConstructedWith($handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager);
		$this->handle($request)->shouldBeHttpResponse(200, null, null, null);
	}
}
