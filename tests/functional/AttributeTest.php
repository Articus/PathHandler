<?php
namespace Test\PathHandler;

use Articus\PathHandler as PH;
use Articus\DataTransfer as DT;
use Interop\Container\ContainerInterface;
use Prophecy\Argument;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface as Request;

class AttributeTest extends \Codeception\Test\Unit
{
	use RequestTrait;
	/**
	 * @var \Test\PathHandler\FunctionalTester
	 */
	protected $tester;

	protected function _after()
	{
		$this->verifyMockObjects();
	}

	public function provideSimpleHandlers()
	{
		$request = $this->createRequest('GET', '/test', [], [], '');
		return [
			'from get' => [
				PH\Attribute\Transfer::SOURCE_GET,
				$request->withQueryParams(['test' => PH\Attribute\Transfer::SOURCE_GET]),
			],
			'from post' => [
				PH\Attribute\Transfer::SOURCE_POST,
				$request->withParsedBody(['test' => PH\Attribute\Transfer::SOURCE_POST]),
			],
			'from route' => [
				PH\Attribute\Transfer::SOURCE_ROUTE,
				$request->withAttribute(
					RouteResult::class,
					RouteResult::fromRoute(
						new Route('test', '', Route::HTTP_METHOD_ANY,'test'),
						['test' => PH\Attribute\Transfer::SOURCE_ROUTE]
					)
				),
			],
			'from headers' => [
				PH\Attribute\Transfer::SOURCE_HEADER,
				$request->withHeader('test', PH\Attribute\Transfer::SOURCE_HEADER),
			],
			'from attributes' => [
				PH\Attribute\Transfer::SOURCE_ATTRIBUTE,
				$request->withAttribute('test', PH\Attribute\Transfer::SOURCE_ATTRIBUTE),
			],
		];
	}

	/**
	 * @dataProvider provideSimpleHandlers
	 */
	public function testTransferAttributeSuccessfulTransfer($source, $request)
	{
		$tester = $this->tester;

		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$dtServiceProphecy
			->transfer(Argument::any(), Argument::any())
			->will(function($args) use (&$tester)
			{
				$tester->assertInternalType('array', $args[0]);
				$tester->assertArrayHasKey('test', $args[0]);
				$tester->assertInstanceOf(Sample\DTO\Transfer::class, $args[1]);
				$args[1]->test = $args[0]['test'];
				return [];
			})
			->shouldBeCalledTimes(1)
		;

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());

		$factory = new PH\Attribute\Factory();
		$options = [
			'source' => $source,
			'type' => Sample\DTO\Transfer::class,
			'objectAttr' => 'object',
			'errorAttr' => 'errors',
		];
		/** @var PH\Attribute\Transfer $attribute */
		$attribute = $factory($containerProphecy->reveal(), PH\Attribute\Transfer::class, $options);
		$tester->assertInstanceOf(PH\Attribute\Transfer::class, $attribute);
		/** @var Request $updatedRequest */
		$updatedRequest = $attribute($request);
		$tester->assertInstanceOf(Request::class, $updatedRequest);
		$object = $updatedRequest->getAttribute('object');
		$tester->assertInstanceOf(Sample\DTO\Transfer::class, $object);
		$tester->assertEquals($source, $object->test);
	}

	/**
	 * @dataProvider provideSimpleHandlers
	 */
	public function testTransferAttributeFailedTransfer($source, $request)
	{
		$tester = $this->tester;

		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$dtServiceProphecy
			->transfer(Argument::any(), Argument::any())
			->will(function($args) use (&$tester)
			{
				$tester->assertInternalType('array', $args[0]);
				$tester->assertArrayHasKey('test', $args[0]);
				$tester->assertInstanceOf(Sample\DTO\Transfer::class, $args[1]);
				return ['test' => 'error'];
			})
			->shouldBeCalledTimes(1)
		;

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());

		$factory = new PH\Attribute\Factory();
		$options = [
			'source' => $source,
			'type' => Sample\DTO\Transfer::class,
			'objectAttr' => 'object',
			'errorAttr' => 'errors',
		];
		/** @var PH\Attribute\Transfer $attribute */
		$attribute = $factory($containerProphecy->reveal(), PH\Attribute\Transfer::class, $options);
		$tester->assertInstanceOf(PH\Attribute\Transfer::class, $attribute);
		/** @var Request $updatedRequest */
		$updatedRequest = $attribute($request);
		$tester->assertInstanceOf(Request::class, $updatedRequest);
		$object = $updatedRequest->getAttribute('object');
		$tester->assertNull($object);
		$errors = $updatedRequest->getAttribute('errors');
		$tester->assertEquals(['test' => 'error'], $errors);
	}

	/**
	 * @dataProvider provideSimpleHandlers
	 */
	public function testTransferAttributeFailedTransferWithException($source, $request)
	{
		$tester = $this->tester;

		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$dtServiceProphecy
			->transfer(Argument::any(), Argument::any())
			->will(function($args) use (&$tester)
			{
				$tester->assertInternalType('array', $args[0]);
				$tester->assertArrayHasKey('test', $args[0]);
				$tester->assertInstanceOf(Sample\DTO\Transfer::class, $args[1]);
				return ['test' => 'error'];
			})
			->shouldBeCalledTimes(1)
		;

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());

		$factory = new PH\Attribute\Factory();
		$options = [
			'source' => $source,
			'type' => Sample\DTO\Transfer::class,
			'objectAttr' => 'object',
			'errorAttr' => null,
		];
		/** @var PH\Attribute\Transfer $attribute */
		$attribute = $factory($containerProphecy->reveal(), PH\Attribute\Transfer::class, $options);
		$tester->assertInstanceOf(PH\Attribute\Transfer::class, $attribute);
		$exception = new PH\Exception\UnprocessableEntity(['test' => 'error']);

		$tester->expectException($exception, function() use (&$attribute, &$request)
		{
			$attribute($request);
		});
	}

	public function testTransferAttributeForPostWithInvalidBody()
	{
		$tester = $this->tester;
		$request = $this->createRequest('POST', '/test', [], [], '');

		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$dtServiceProphecy
			->transfer(Argument::any(), Argument::any())
			->shouldNotBeCalled()
		;

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());

		$factory = new PH\Attribute\Factory();
		$options = [
			'source' => PH\Attribute\Transfer::SOURCE_POST,
			'type' => Sample\DTO\Transfer::class,
		];
		/** @var PH\Attribute\Transfer $attribute */
		$attribute = $factory($containerProphecy->reveal(), PH\Attribute\Transfer::class, $options);
		$tester->assertInstanceOf(PH\Attribute\Transfer::class, $attribute);
		$exception = new PH\Exception\BadRequest('Unexpected content');

		$tester->expectException($exception, function() use (&$attribute, &$request)
		{
			$attribute($request);
		});
	}

	public function testTransferAttributeForRouteWithoutRouting()
	{
		$tester = $this->tester;
		$request = $this->createRequest('POST', '/test', [], [], '');

		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$dtServiceProphecy
			->transfer(Argument::any(), Argument::any())
			->shouldNotBeCalled()
		;

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());

		$factory = new PH\Attribute\Factory();
		$options = [
			'source' => PH\Attribute\Transfer::SOURCE_ROUTE,
			'type' => Sample\DTO\Transfer::class,
		];
		/** @var PH\Attribute\Transfer $attribute */
		$attribute = $factory($containerProphecy->reveal(), PH\Attribute\Transfer::class, $options);
		$tester->assertInstanceOf(PH\Attribute\Transfer::class, $attribute);
		$exception = new \LogicException('Failed to find routing result.');

		$tester->expectException($exception, function() use (&$attribute, &$request)
		{
			$attribute($request);
		});
	}

	public function testTransferAttributeForUnknownSource()
	{
		$tester = $this->tester;
		$request = $this->createRequest('POST', '/test', [], [], '');

		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$dtServiceProphecy
			->transfer(Argument::any(), Argument::any())
			->shouldNotBeCalled()
		;

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());

		$factory = new PH\Attribute\Factory();
		$options = [
			'source' => 'something',
			'type' => Sample\DTO\Transfer::class,
		];
		/** @var PH\Attribute\Transfer $attribute */
		$attribute = $factory($containerProphecy->reveal(), PH\Attribute\Transfer::class, $options);
		$tester->assertInstanceOf(PH\Attribute\Transfer::class, $attribute);
		$exception = new \InvalidArgumentException('Unknown source something.');

		$tester->expectException($exception, function() use (&$attribute, &$request)
		{
			$attribute($request);
		});
	}

	public function testTransferAttributeForUnknownObjectClass()
	{
		$tester = $this->tester;
		$request = $this->createRequest('GET', '/test', [], [], '');

		$dtServiceProphecy = $this->prophesize(DT\Service::class);
		$dtServiceProphecy
			->transfer(Argument::any(), Argument::any())
			->shouldNotBeCalled()
		;

		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get(DT\Service::class)->willReturn($dtServiceProphecy->reveal());

		$factory = new PH\Attribute\Factory();
		$options = [
			'source' => PH\Attribute\Transfer::SOURCE_GET,
			'type' => 'unknown\\class',
		];
		/** @var PH\Attribute\Transfer $attribute */
		$attribute = $factory($containerProphecy->reveal(), PH\Attribute\Transfer::class, $options);
		$tester->assertInstanceOf(PH\Attribute\Transfer::class, $attribute);
		$exception = new \InvalidArgumentException('Unknown class unknown\\class.');

		$tester->expectException($exception, function() use (&$attribute, &$request)
		{
			$attribute($request);
		});
	}
}