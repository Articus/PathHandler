<?php
namespace Test\PathHandler;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Prophecy\Argument;
use Prophecy\Prophecy;
use Test\PathHandler\Sample;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Stream;

class MiddlewareTest extends \Codeception\Test\Unit
{
	use RequestTrait;
	use RouterTrait;
	/**
	 * @var \Test\PathHandler\FunctionalTester
	 */
	protected $tester;

	protected function _after()
	{
		$this->verifyMockObjects();
	}

	/**
	 * @return DelegateInterface
	 */
	protected function createDelegator()
	{
		$delegatorProphecy = $this->prophesize(DelegateInterface::class);
		$delegatorProphecy
			->process(Argument::type(Request::class))
			->shouldNotBeCalled()
		;
		return $delegatorProphecy->reveal();
	}

	/**
	 * @param string|Prophecy\ObjectProphecy $handler
	 * @param Prophecy\ObjectProphecy[] $consumers
	 * @param Prophecy\ObjectProphecy[] $attributes
	 * @param Prophecy\ObjectProphecy[] $producers
	 * @return PH\Middleware
	 */
	protected function createMiddleware($handler, array $consumers, array $attributes, array $producers)
	{
		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$config = [
			'path_handler' => [
				'routes' => 'router',
				'handlers' => [
					'invokables' => [],
					'factories' => [],
				],
				'metadata_cache' => [
					'adapter' => 'memory',
				],
				'consumers' => [
					'factories' => [],
				],
				'attributes' => [
					'factories' => [],
				],
				'producers' => [
					'factories' => [],
				],
			],
		];

		$tester = $this->tester;

		if (is_string($handler))
		{
			$config['path_handler']['handlers']['invokables']['Test'] = $handler;
		}
		elseif ($handler instanceof Prophecy\ObjectProphecy)
		{
			$config['path_handler']['handlers']['factories']['Test'] = function($container, $name, $options) use (&$handler)
			{
				return $handler->reveal();
			};
		}

		foreach ($consumers as $key => &$consumer)
		{
			$config['path_handler']['consumers']['factories'][$key] = function ($container, $name, $options) use (&$consumer, &$tester)
			{
				$tester->assertEquals(['test' => 'consume'], $options);
				return $consumer->reveal();
			};
		}
		foreach ($attributes as $key => &$attribute)
		{
			$config['path_handler']['attributes']['factories'][$key] = function ($container, $name, $options) use (&$attribute, &$tester)
			{
				$tester->assertEquals(['test' => 'attribute'], $options);
				return $attribute->reveal();
			};
		}
		foreach ($producers as $key => &$producer)
		{
			$config['path_handler']['producers']['factories'][$key] = function ($container, $name, $options) use (&$producer, &$tester)
			{
				$tester->assertEquals(['test' => 'produce'], $options);
				return $producer->reveal();
			};
		}

		$containerProphecy->get('config')->willReturn($config);

		$router = $this->createRouter(
			'router',
			[
				'main' => [
					'type' => 'Literal',
					'options' => [
						'route' => '/test',
						'defaults' => [
							'handler' => 'Test',
						]
					]
				]
			]
		);
		$containerProphecy->has('router')->willReturn(true);
		$containerProphecy->get('router')->willReturn($router);

		$container = $containerProphecy->reveal();

		$factory = new PH\MiddlewareFactory('path_handler');
		return $factory($container, PH\Middleware::class);
	}


	public function provideSimpleHandlers()
	{
		return [
			'get' => ['GET', PH\Operation\GetInterface::class, PH\Operation\MethodEnum::GET],
			'post' => ['POST', PH\Operation\PostInterface::class, PH\Operation\MethodEnum::POST],
			'patch' => ['PATCH', PH\Operation\PatchInterface::class, PH\Operation\MethodEnum::PATCH],
			'put' => ['PUT', PH\Operation\PutInterface::class, PH\Operation\MethodEnum::PUT],
			'delete' => ['DELETE', PH\Operation\DeleteInterface::class, PH\Operation\MethodEnum::DELETE],
		];
	}
	/**
	 * @dataProvider provideSimpleHandlers
	 */
	public function testSimpleHandler($httpMethod, $interfaceName, $interfaceMethod)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			$httpMethod,
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$handlerProphecy = $this->prophesize($interfaceName);
		$handlerProphecy->addMethodProphecy(
			(new Prophecy\MethodProphecy($handlerProphecy, $interfaceMethod, [Argument::type(Request::class)]))
				->willReturn('test:payload')
				->shouldBeCalledTimes(1)
		);

		$middleware = $this->createMiddleware($handlerProphecy, [], [], []);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(200, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());
	}

	/**
	 * @dataProvider provideSimpleHandlers
	 */
	public function testExceptionThrownInHandler($httpMethod, $interfaceName, $interfaceMethod)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			$httpMethod,
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);
		$delegator = $this->createDelegator();

		$exception = new \Exception('Low level problem');
		$handlerProphecy = $this->prophesize($interfaceName);
		$handlerProphecy->addMethodProphecy(
			(new Prophecy\MethodProphecy($handlerProphecy, $interfaceMethod, [Argument::type(Request::class)]))
				->willThrow($exception)
				->shouldBeCalledTimes(1)
		);

		$middleware = $this->createMiddleware($handlerProphecy, [], [], []);

		$tester->expectException($exception, function () use (&$request, &$delegator, &$middleware)
		{
			$middleware->process($request, $delegator);
		});
	}


	public function provideHandlersWithConsumer()
	{
		return [
			'declared for method' => [Sample\Handler\PostWithConsumer::class],
			'declared for class' => [Sample\Handler\PostWithCommonConsumer::class],
		];
	}
	/**
	 * @dataProvider provideHandlersWithConsumer
	 */
	public function testPostHandlerWithConsumer($handler)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$consumerProphecy = $this->prophesize(PH\Consumer\ConsumerInterface::class);
		$consumerProphecy
			->parse(Argument::type(Stream::class), Argument::any(), Argument::any(), Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals('test:123', $args[0]->getContents());
				return ['test' => 123];
			})
			->shouldBeCalledTimes(1)
		;

		$middleware = $this->createMiddleware($handler, ['Test' => $consumerProphecy], [], []);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(200, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());
	}

	/**
	 * @dataProvider provideHandlersWithConsumer
	 */
	public function testConsumerDoesNotSupportedInputBodyType($handler)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'something/unknown',
			],
			[],
			'test:123'
		);

		$consumerProphecy = $this->prophesize(PH\Consumer\ConsumerInterface::class);
		$consumerProphecy
			->parse(Argument::type(Stream::class), Argument::any(), Argument::any(), Argument::any())
			->shouldNotBeCalled()
		;

		$middleware = $this->createMiddleware($handler, ['Test' => $consumerProphecy], [], []);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(415, $response->getStatusCode());
		$tester->assertEquals('Unsupported media type', $response->getReasonPhrase());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());

	}

	public function provideHandlersWithSeveralConsumers()
	{
		return [
			'Only first can parse body' => [Sample\Handler\PostWithSeveralConsumers::class, 'application/json'],
			'Only second can parse body' => [Sample\Handler\PostWithSeveralConsumers::class, 'text/html'],
			'First by default priority can parse body' => [Sample\Handler\PostWithDefaultPriorityConsumers::class, 'application/json'],
			'First by set priority can parse body' => [Sample\Handler\PostWithPriorityConsumers::class, 'application/json'],
		];
	}
	/**
	 * @dataProvider provideHandlersWithSeveralConsumers
	 */
	public function testPostHandlerWithSeveralConsumers($handler, $mediaType)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => $mediaType,
			],
			[],
			'test:123'
		);

		$consumerProphecy = $this->prophesize(PH\Consumer\ConsumerInterface::class);
		$consumerProphecy
			->parse(Argument::type(Stream::class), Argument::any(), Argument::any(), Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals('test:123', $args[0]->getContents());
				return ['test' => 123];
			})
			->shouldBeCalled()
		;

		$lowConsumerProphecy = $this->prophesize(PH\Consumer\ConsumerInterface::class);
		$lowConsumerProphecy
			->parse(Argument::type(Stream::class), Argument::any(), Argument::any(), Argument::any())
			->shouldNotBeCalled()
		;

		$middleware = $this->createMiddleware(
			$handler,
			['Test' => $consumerProphecy, 'TestLow' => $lowConsumerProphecy],
			[],
			[]
		);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(200, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());

	}

	public function provideHandlersWithAttribute()
	{
		return [
			'declared for method' => [Sample\Handler\PostWithAttribute::class],
			'declared for class' => [Sample\Handler\PostWithCommonAttribute::class],
		];
	}
	/**
	 * @dataProvider provideHandlersWithAttribute
	 */
	public function testPostHandlerWithAttribute($handler)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$attributeProphecy = $this->prophesize(PH\Attribute\AttributeInterface::class);
		$attributeProphecy
			->__invoke(Argument::type(Request::class))
			->will(function ($args) use (&$tester)
			{
				return $args[0];
			})
			->shouldBeCalledTimes(1)
		;

		$middleware = $this->createMiddleware($handler, [], ['Test' => $attributeProphecy], []);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(200, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());
	}

	public function provideHandlersWithSeveralAttributes()
	{
		return [
			'Order by default priority' => [Sample\Handler\PostWithDefaultPriorityAttributes::class],
			'Order by set priority' => [Sample\Handler\PostWithPriorityAttributes::class],
		];
	}
	/**
	 * @dataProvider provideHandlersWithSeveralAttributes
	 */
	public function testPostHandlerWithSeveralAttributes($handler)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$attributeProphecy = $this->prophesize(PH\Attribute\AttributeInterface::class);
		/** @var Prophecy\MethodProphecy $attributeProphecyInvoke */
		$attributeProphecyInvoke = $attributeProphecy
			->__invoke(Argument::type(Request::class))
			->will(function ($args) use (&$tester)
			{
				return $args[0];
			})
			->shouldBeCalledTimes(1)
		;

		$lowAttributeProphecy = $this->prophesize(PH\Attribute\AttributeInterface::class);
		$lowAttributeProphecy
			->__invoke(Argument::type(Request::class))
			->will(function ($args) use (&$tester, &$attributeProphecyInvoke)
			{
				$attributeProphecyInvoke->shouldHaveBeenCalledTimes(1);
				return $args[0];
			})
			->shouldBeCalledTimes(1)
		;

		$middleware = $this->createMiddleware($handler, [], ['Test' => $attributeProphecy, 'TestLow' => $lowAttributeProphecy], []);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(200, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());

	}

	public function provideHandlersWithProducer()
	{
		return [
			'declared for method' => [Sample\Handler\PostWithProducer::class],
			'declared for class' => [Sample\Handler\PostWithCommonProducer::class],
		];
	}
	/**
	 * @dataProvider provideHandlersWithProducer
	 */
	public function testPostHandlerWithProducer($handler)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$producerProphecy = $this->prophesize(PH\Producer\ProducerInterface::class);
		$producerProphecy
			->assemble(Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals(['test' => 'payload'], $args[0]);
				$result = new Stream('php://temp', 'wb+');
				$result->write('test:payload');
				$result->rewind();
				return $result;
			})
			->shouldBeCalledTimes(1)
		;

		$middleware = $this->createMiddleware($handler, [], [], ['Test' => $producerProphecy]);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(200, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEquals('test:payload', $response->getBody()->getContents());
		$tester->assertEquals(['application/json'], $response->getHeader('Content-Type'));
	}

	/**
	 * @dataProvider provideHandlersWithProducer
	 */
	public function testPostHandlerWithHeaderProducer($handler)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$producerProphecy = $this->prophesize(PH\Producer\ProducerInterface::class);
		$producerProphecy->willImplement(PH\Producer\HeaderInterface::class);
		$producerProphecy
			->assemble(Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals(['test' => 'payload'], $args[0]);
				$result = new Stream('php://temp', 'wb+');
				$result->write('test:payload');
				$result->rewind();
				return $result;
			})
			->shouldBeCalledTimes(1)
		;
		$producerProphecy
			->assembleHeaders(Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals(['test' => 'payload'], $args[0]);
				yield 'x-test' => 'header';
			})
			->shouldBeCalledTimes(1)
		;


		$middleware = $this->createMiddleware($handler, [], [], ['Test' => $producerProphecy]);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(200, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEquals('test:payload', $response->getBody()->getContents());
		$tester->assertEquals(['application/json'], $response->getHeader('Content-Type'));
		$tester->assertEquals(['header'], $response->getHeader('x-test'));
	}

	/**
	 * @dataProvider provideHandlersWithProducer
	 */
	public function testUnacceptableOutputBodyType($handler)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'something/unknown',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$producerProphecy = $this->prophesize(PH\Producer\ProducerInterface::class);
		$producerProphecy
			->assemble(Argument::any())
			->shouldNotBeCalled()
		;

		$middleware = $this->createMiddleware($handler, [], [], ['Test' => $producerProphecy]);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(406, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());
	}

	public function provideHandlersWithSeveralProducers()
	{
		return [
			'Only first can prepare body' => [Sample\Handler\PostWithSeveralProducers::class, 'application/json'],
			'Only second can prepare body' => [Sample\Handler\PostWithSeveralProducers::class, 'text/html'],
			'First by default priority can prepare body' => [Sample\Handler\PostWithDefaultPriorityProducers::class, 'application/json'],
			'First by set priority can prepare body' => [Sample\Handler\PostWithPriorityProducers::class, 'application/json'],
		];
	}
	/**
	 * @dataProvider provideHandlersWithSeveralProducers
	 */
	public function testPostHandlerWithSeveralProducers($handler, $mediaType)
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => $mediaType,
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$producerProphecy = $this->prophesize(PH\Producer\ProducerInterface::class);
		$producerProphecy
			->assemble(Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals(['test' => 'payload'], $args[0]);
				$result = new Stream('php://temp', 'wb+');
				$result->write('test:payload');
				$result->rewind();
				return $result;
			})
			->shouldBeCalledTimes(1)
		;
		$lowProducerProphecy = $this->prophesize(PH\Producer\ProducerInterface::class);
		$lowProducerProphecy
			->assemble(Argument::any())
			->shouldNotBeCalled()
		;

		$middleware = $this->createMiddleware($handler, [], [], ['Test' => $producerProphecy, 'TestLow' => $lowProducerProphecy]);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(200, $response->getStatusCode());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEquals('test:payload', $response->getBody()->getContents());
		$tester->assertEquals([$mediaType], $response->getHeader('Content-Type'));
	}

	public function testUnknownPath()
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'GET',
			'/unknown',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$handlerProphecy = $this->prophesize(PH\Operation\GetInterface::class);
		$handlerProphecy
			->handleGet(Argument::type(Request::class))
			->shouldNotBeCalled()
		;

		$middleware = $this->createMiddleware($handlerProphecy, [], [], []);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(404, $response->getStatusCode());
		$tester->assertEquals('Not found', $response->getReasonPhrase());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());
	}

	public function testUnsupportedHttpMethod()
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$handlerProphecy = $this->prophesize(PH\Operation\GetInterface::class);
		$handlerProphecy
			->handleGet(Argument::type(Request::class))
			->shouldNotBeCalled()
		;

		$middleware = $this->createMiddleware($handlerProphecy, [], [], []);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(405, $response->getStatusCode());
		$tester->assertEquals('Method not allowed', $response->getReasonPhrase());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEmpty($response->getBody()->getContents());
	}

	public function testPostHandlerWithProducerThrowingHttpCodeException()
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$producerProphecy = $this->prophesize(PH\Producer\ProducerInterface::class);
		$producerProphecy
			->assemble(Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals(['test' => 'payload'], $args[0]);
				$result = new Stream('php://temp', 'wb+');
				$result->write('test:payload');
				$result->rewind();
				return $result;
			})
			->shouldBeCalledTimes(1)
		;

		$middleware = $this->createMiddleware(Sample\Handler\PostWithProducerForException::class, [], [], ['Test' => $producerProphecy]);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(123, $response->getStatusCode());
		$tester->assertEquals('Test reason', $response->getReasonPhrase());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEquals('test:payload', $response->getBody()->getContents());
		$tester->assertEquals(['application/json'], $response->getHeader('Content-Type'));
	}

	public function testPostHandlerWithProducerThrowingHttpCodeHeaderException()
	{
		$tester = $this->tester;
		$request = $this->createRequest(
			'POST',
			'/test',
			[
				'accept' => 'application/json',
				'content-type' => 'application/json',
			],
			[],
			'test:123'
		);

		$producerProphecy = $this->prophesize(PH\Producer\ProducerInterface::class);
		$producerProphecy
			->assemble(Argument::any())
			->will(function ($args) use (&$tester)
			{
				$tester->assertEquals(['test' => 'payload'], $args[0]);
				$result = new Stream('php://temp', 'wb+');
				$result->write('test:payload');
				$result->rewind();
				return $result;
			})
			->shouldBeCalledTimes(1)
		;

		$middleware = $this->createMiddleware(Sample\Handler\PostWithProducerForHeaderException::class, [], [], ['Test' => $producerProphecy]);

		$response = $middleware->process($request, $this->createDelegator());
		$tester->assertInstanceOf(Response::class, $response);
		$tester->assertEquals(123, $response->getStatusCode());
		$tester->assertEquals('Test reason', $response->getReasonPhrase());
		$tester->assertInstanceOf(Stream::class, $response->getBody());
		$tester->assertEquals('test:payload', $response->getBody()->getContents());
		$tester->assertEquals(['application/json'], $response->getHeader('Content-Type'));
		$tester->assertEquals(['header'], $response->getHeader('x-test'));
	}
}