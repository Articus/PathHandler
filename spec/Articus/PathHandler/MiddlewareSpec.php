<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler;

use Articus\PathHandler as PH;
use Articus\PluginManager as PM;
use LogicException;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\StreamInterface;
use function array_diff;
use function array_diff_key;
use function array_keys;
use function get_debug_type;
use function implode;
use function sprintf;

class MiddlewareSpec extends ObjectBehavior
{
	public function getMatchers(): array
	{
		return [
			'beHttpResponse' => function($subject, ?int $code, ?string $reason, ?array $requiredHeaders, ?StreamInterface $body)
			{
				if (!($subject instanceof Response))
				{
					throw new FailureException(sprintf(
						'Invalid response: expecting %s, not %s.', Response::class, get_debug_type($subject)
					));
				}
				if (($code !== null) && ($subject->getStatusCode() !== $code))
				{
					throw new FailureException(sprintf(
						'Invalid response status code: expecting %s, not %s.', $code, $subject->getStatusCode()
					));
				}
				if (($reason !== null) && ($subject->getReasonPhrase() !== $reason))
				{
					throw new FailureException(sprintf(
						'Invalid response reason phrase: expecting %s, not %s.', $reason, $subject->getReasonPhrase()
					));
				}
				if ($requiredHeaders !== null)
				{
					$responseHeaders = $subject->getHeaders();
					$missingHeaders = array_diff_key($requiredHeaders, $responseHeaders);
					if (!empty($missingHeaders))
					{
						throw new FailureException(sprintf(
							'Invalid response headers: missing %s.', implode(', ', array_keys($missingHeaders))
						));
					}
					foreach ($requiredHeaders as $name => $values)
					{
						$missingValues = array_diff($values, $responseHeaders[$name]);
						if (!empty($missingValues))
						{
							throw new FailureException(sprintf(
								'Invalid response header %s: missing values %s.', $name, implode(', ', array_keys($missingHeaders))
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

	public function it_generates_successful_response_with_default_producer_if_there_is_no_consumers_attributes_producers(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		Response $response,
		Response $response1,
		Response $response2,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$emptyGenerator = function()
		{
			yield from [];
		};
		$handlerData = ['test' => 123];
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];
		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response2);
	}

	public function it_throws_on_invalid_default_producer(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		Response $response,
		ResponseFactoryInterface $responseFactory
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$emptyGenerator = function()
		{
			yield from [];
		};
		$handlerData = ['test' => 123];
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];
		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->shouldThrow(LogicException::class)->during('handle', [$request]);
	}

	public function it_generates_successful_response_with_first_producer_if_there_is_no_accept(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		Response $response,
		Response $response1,
		Response $response2,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->__invoke($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn('');

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $producerMetadata[0][0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);

		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, []
		);
		$this->handle($request)->shouldBe($response2);
	}

	public function it_generates_bad_request_response_with_default_producer_on_invalid_accept_if_there_is_producer(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];

		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn("invalid\rvalue");

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble('Invalid Accept header')->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withStatus(400, 'Bad request')->shouldBeCalledOnce()->willReturn($response3);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response3);
	}

	public function it_generates_not_acceptable_response_if_producer_does_not_match_accept(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];

		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn('test/mime');

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble(null)->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withStatus(406, 'Not acceptable')->shouldBeCalledOnce()->willReturn($response3);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response3);
	}

	public function it_throws_on_invalid_producer_if_it_matches_accept(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		ResponseFactoryInterface $responseFactory
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

		$producerManager->__invoke($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn(null);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[0][0]);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, []
		);
		$this->shouldThrow(LogicException::class)->during('handle', [$request]);
	}

	public function it_generates_successful_response_with_first_producer_that_matches_accept(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		Response $response,
		Response $response1,
		Response $response2,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->__invoke($producerMetadata[1][1], $producerMetadata[1][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[1][0]);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $producerMetadata[1][0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);

		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, []
		);
		$this->handle($request)->shouldBe($response2);
	}

	public function it_generates_successful_response_with_extra_headers_if_producer_provides_them(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		Response $response4,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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
		$producerHeaderNames = ['test_1', 'test_2'];
		$producerHeaders = [
			$producerHeaderNames[0] => 'val_1',
			$producerHeaderNames[1] => ['val_2_1', 'val_2_2'],
		];
		$producerHeaderGenerator = function () use ($producerHeaders)
		{
			yield from $producerHeaders;
		};

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->__invoke($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[0][0]);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $producerMetadata[0][0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withHeader($producerHeaderNames[0], $producerHeaders[$producerHeaderNames[0]])->shouldBeCalledOnce()->willReturn($response3);
		$response3->withHeader($producerHeaderNames[1], $producerHeaders[$producerHeaderNames[1]])->shouldBeCalledOnce()->willReturn($response4);

		$producer->implement(PH\Producer\HeaderInterface::class);
		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);
		$producer->assembleHeaders($handlerData)->shouldBeCalledOnce()->will($producerHeaderGenerator);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, []
		);
		$this->handle($request)->shouldBe($response4);
	}

	public function it_generates_response_with_payload_on_http_code_exception_if_there_is_producer(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		PH\Exception\HttpCode $exception,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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

		$exception->beConstructedWith([$exceptionCode, $exceptionReason, $exceptionPayload]);
		$exception->getPayload()->shouldBeCalledOnce()->willReturn($exceptionPayload);

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willThrow($exception->getWrappedObject());

		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->__invoke($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[0][0]);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $producerMetadata[0][0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withStatus($exceptionCode, $exceptionReason)->shouldBeCalledOnce()->willReturn($response3);

		$producer->assemble($exceptionPayload)->shouldBeCalledOnce()->willReturn($responseBody);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, []
		);
		$this->handle($request)->shouldBe($response3);
	}

	public function it_generates_response_with_extra_headers_on_http_code_exception_providing_them_if_there_is_producer(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		$handler,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		Response $response4,
		Response $response5,
		PH\Exception\HttpCode $exception,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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
		$exceptionHeaderNames = ['test_1', 'test_2'];
		$exceptionHeaders = [
			$exceptionHeaderNames[0] => 'val_1',
			$exceptionHeaderNames[1] => ['val_2_1', 'val_2_2'],
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

		$exception->beConstructedWith([$exceptionCode, $exceptionReason, $exceptionPayload]);
		$exception->implement(PH\Exception\HeaderInterface::class);
		$exception->getPayload()->shouldBeCalledOnce()->willReturn($exceptionPayload);
		$exception->getHeaders()->shouldBeCalledOnce()->will($exceptionHeaderGenerator);

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($producerMetadataGenerator);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willThrow($exception->getWrappedObject());

		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);
		$producerManager->__invoke($producerMetadata[0][1], $producerMetadata[0][2])->shouldBeCalledOnce()->willReturn($producer);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->getHeaderLine('Accept')->shouldBeCalledOnce()->willReturn($producerMetadata[0][0]);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $producerMetadata[0][0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withHeader($exceptionHeaderNames[0], $exceptionHeaders[$exceptionHeaderNames[0]])->shouldBeCalledOnce()->willReturn($response3);
		$response3->withHeader($exceptionHeaderNames[1], $exceptionHeaders[$exceptionHeaderNames[1]])->shouldBeCalledOnce()->willReturn($response4);
		$response4->withStatus($exceptionCode, $exceptionReason)->shouldBeCalledOnce()->willReturn($response5);

		$producer->assemble($exceptionPayload)->shouldBeCalledOnce()->willReturn($responseBody);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, []
		);
		$this->handle($request)->shouldBe($response5);
	}


	public function it_generates_bad_request_if_there_is_consumer_and_no_content_type(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(false);

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble('Content-Type header should be declared')->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withStatus(400, 'Bad request')->shouldBeCalledOnce()->willReturn($response3);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response3);
	}

	public function it_generates_bad_request_response_if_there_is_consumer_and_several_content_types(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn(['test_1/mime', 'test_2/mime']);

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble('Multiple Content-Type headers are not allowed')->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withStatus(400, 'Bad request')->shouldBeCalledOnce()->willReturn($response3);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response3);
	}

	public function it_generates_bad_request_response_on_invalid_content_type_if_there_is_consumer(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
	)
	{
		$handlerName = 'test_handler';
		$httpMethod = 'TEST';
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn(["invalid\rvalue"]);
		$request->getHeaderLine('Content-Type')->shouldBeCalledOnce()->willReturn("invalid\rvalue");

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble('Invalid Content-Type header')->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withStatus(400, 'Bad request')->shouldBeCalledOnce()->willReturn($response3);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response3);
	}

	public function it_generates_unsupported_media_type_response_if_consumer_does_not_match_content_type(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		Response $response1,
		Response $response2,
		Response $response3,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($consumerMetadataGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn(['test/mime']);
		$request->getHeaderLine('Content-Type')->shouldBeCalledOnce()->willReturn('test/mime');

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble(null)->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);
		$response2->withStatus(415, 'Unsupported media type')->shouldBeCalledOnce()->willReturn($response3);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response3);
	}

	public function it_throws_on_invalid_consumer_if_it_matches_content_type(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		ResponseFactoryInterface $responseFactory
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

		$consumerManager->__invoke($consumerMetadata[0][1], $consumerMetadata[0][2])->shouldBeCalledOnce()->willReturn(null);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn([$consumerMetadata[0][0]]);
		$request->getHeaderLine('Content-Type')->shouldBeCalledOnce()->willReturn($consumerMetadata[0][0]);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, []
		);
		$this->shouldThrow(LogicException::class)->during('handle', [$request]);
	}

	public function it_parses_body_with_first_consumer_that_matches_content_type(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		StreamInterface $requestBody,
		$handler,
		PH\Consumer\ConsumerInterface $consumer,
		Response $response,
		Response $response1,
		Response $response2,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(true);
		$metadataProvider->getConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->will($consumerMetadataGenerator);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($emptyGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request)->shouldBeCalledOnce()->willReturn($handlerData);

		$consumerManager->__invoke($consumerMetadata[1][1], $consumerMetadata[1][2])->shouldBeCalledOnce()->willReturn($consumer);
		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);
		$request->hasHeader('Content-Type')->shouldBeCalledOnce()->willReturn(true);
		$request->getHeader('Content-Type')->shouldBeCalledOnce()->willReturn([$consumerMetadata[1][0]]);
		$request->getHeaderLine('Content-Type')->shouldBeCalledOnce()->willReturn($consumerMetadata[1][0]);
		$request->getBody()->shouldBeCalledOnce()->willReturn($requestBody);
		$request->getParsedBody()->shouldBeCalledOnce()->willReturn($requestData);
		$request->withAttribute(PH\Middleware::PARSED_BODY_ATTR_NAME, $consumerData)->shouldBeCalledOnce()->willReturn($request);

		$consumer->parse($requestBody, $requestData, $consumerMetadata[1][0], [])->shouldBeCalledOnce()->willReturn($consumerData);

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response2);
	}

	public function it_throws_on_invalid_attribute(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Response $response,
		ResponseFactoryInterface $responseFactory
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

		$attributeManager->__invoke($attributeMetadata[0][0], $attributeMetadata[0][1])->shouldBeCalledOnce()->willReturn(null);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, []
		);
		$this->shouldThrow(LogicException::class)->during('handle', [$request]);
	}

	public function it_attributes_request_with_all_attributes_in_order(
		PH\MetadataProviderInterface $metadataProvider,
		PM\PluginManagerInterface $handlerManager,
		PM\PluginManagerInterface $consumerManager,
		PM\PluginManagerInterface $attributeManager,
		PM\PluginManagerInterface $producerManager,
		Request $request,
		Request $request1,
		Request $request2,
		Request $request3,
		$handler,
		PH\Attribute\AttributeInterface $attribute1,
		PH\Attribute\AttributeInterface $attribute2,
		PH\Attribute\AttributeInterface $attribute3,
		Response $response,
		Response $response1,
		Response $response2,
		PH\Producer\ProducerInterface $producer,
		StreamInterface $responseBody,
		ResponseFactoryInterface $responseFactory
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
		$defaultProducer = ['test/mime', 'test_producer', ['test_option' => 123]];

		$metadataProvider->hasConsumers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->getAttributes($handlerName, $httpMethod)->shouldBeCalledOnce()->will($attributeMetadataGenerator);
		$metadataProvider->hasProducers($handlerName, $httpMethod)->shouldBeCalledOnce()->willReturn(false);
		$metadataProvider->executeHandlerMethod($handlerName, $httpMethod, $handler, $request3)->shouldBeCalledOnce()->willReturn($handlerData);

		$handlerManager->__invoke($handlerName, [])->shouldBeCalledOnce()->willReturn($handler);
		$attributeManager->__invoke($attributeMetadata[0][0], $attributeMetadata[0][1])->shouldBeCalledOnce()->willReturn($attribute1);
		$attributeManager->__invoke($attributeMetadata[1][0], $attributeMetadata[1][1])->shouldBeCalledOnce()->willReturn($attribute2);
		$attributeManager->__invoke($attributeMetadata[2][0], $attributeMetadata[2][1])->shouldBeCalledOnce()->willReturn($attribute3);

		$request->getMethod()->shouldBeCalledOnce()->willReturn($httpMethod);

		$attribute1->__invoke($request)->shouldBeCalledOnce()->willReturn($request1);
		$attribute2->__invoke($request1)->shouldBeCalledOnce()->willReturn($request2);
		$attribute3->__invoke($request2)->shouldBeCalledOnce()->willReturn($request3);

		$producerManager->__invoke($defaultProducer[1], $defaultProducer[2])->shouldBeCalledOnce()->willReturn($producer);
		$producer->assemble($handlerData)->shouldBeCalledOnce()->willReturn($responseBody);

		$responseFactory->createResponse()->shouldBeCalledOnce()->willReturn($response);
		$response->withHeader('Content-Type', $defaultProducer[0])->shouldBeCalledOnce()->willReturn($response1);
		$response1->withBody($responseBody)->shouldBeCalledOnce()->willReturn($response2);

		$this->beConstructedWith(
			$handlerName, $metadataProvider, $handlerManager, $consumerManager, $attributeManager, $producerManager, $responseFactory, $defaultProducer
		);
		$this->handle($request)->shouldBe($response2);
	}
}
