<?php
namespace Test\PathHandler;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Test\PathHandler\Sample\Handler;
use Zend\Cache\Storage\StorageInterface;
use Zend\Expressive\Router\RouterInterface;

class RouterFactoryTest extends \Codeception\Test\Unit
{
	use RouterTrait;

	/**
	 * @var \Test\PathHandler\FunctionalTester
	 */
	protected $tester;

	protected function createTreeConfig($root)
	{
		return [
			$root => [
				'routes' => [
					'main' => [
						'type' => 'Literal',
						'options' => [
							'route' => '/',
							'defaults' => [
								'handler' => 'Handler',
							]
						]
					]
				]
			]
		];
	}

	protected function createFastConfig($root)
	{
		return [
			$root => [
				'handler_attr' => 'handler',
				'handlers' => [
					Handler\EmptyWithStaticRoute::class,
					Handler\EmptyWithVariableRoute::class,
					Handler\EmptyWithOptionalRoute::class,
				],
				'metadata_cache' => [
					'adapter' => 'blackhole',
				],
			]
		];
	}

	public function provideNormalInvoke()
	{
		$treeConfig = $this->createTreeConfig(PH\Router\TreeConfiguration::class);
		$treeContainerProphecy = $this->prophesize(ContainerInterface::class);
		$treeContainerProphecy->get('config')->willReturn($treeConfig)->shouldBeCalledTimes(1);

		$fastConfig = $this->createFastConfig(PH\Router\FastRouteAnnotation::class);
		$fastContainerProphecy = $this->prophesize(ContainerInterface::class);
		$fastContainerProphecy->get('config')->willReturn($fastConfig)->shouldBeCalledTimes(1);

		return [
			'tree configuration' => [
				PH\Router\Factory\TreeConfiguration::class,
				$treeContainerProphecy->reveal(),
				PH\Router\TreeConfiguration::class,
			],
			'fast route annotation' => [
				PH\Router\Factory\FastRouteAnnotation::class,
				$fastContainerProphecy->reveal(),
				PH\Router\FastRouteAnnotation::class,
			],
		];
	}

	/**
	 * @dataProvider provideNormalInvoke
	 */
	public function testNormalInvoke($factoryClassName, $container, $routerClassName)
	{
		$factory = new $factoryClassName();
		$router = $factory($container, 'test');
		$this->tester->assertInstanceOf($routerClassName, $router);
	}


	public function provideStaticInvokeWithCustomConfig()
	{
		$treeConfigName = 'custom_tree_config';
		$treeConfig = $this->createTreeConfig($treeConfigName);
		$treeContainerProphecy = $this->prophesize(ContainerInterface::class);
		$treeContainerProphecy->get('config')->willReturn($treeConfig)->shouldBeCalledTimes(1);

		$fastConfigName = 'custom_fast_config';
		$fastConfig = $this->createFastConfig($fastConfigName);
		$fastContainerProphecy = $this->prophesize(ContainerInterface::class);
		$fastContainerProphecy->get('config')->willReturn($fastConfig)->shouldBeCalledTimes(1);

		return [
			'tree configuration' => [
				PH\Router\Factory\TreeConfiguration::class,
				$treeConfigName,
				$treeContainerProphecy->reveal(),
				PH\Router\TreeConfiguration::class,
			],
			'fast route annotation' => [
				PH\Router\Factory\FastRouteAnnotation::class,
				$fastConfigName,
				$fastContainerProphecy->reveal(),
				PH\Router\FastRouteAnnotation::class,
			],
		];
	}

	/**
	 * @dataProvider provideStaticInvokeWithCustomConfig
	 */
	public function testStaticInvokeWithCustomConfig($factoryClassName, $customConfigName, $container, $routerClassName)
	{
		$tester = $this->tester;
		//Successful invocation
		$router = call_user_func([$factoryClassName, $customConfigName], $container, 'test', []);
		$tester->assertInstanceOf($routerClassName, $router);
		//Invalid invocation
		$exception = new \InvalidArgumentException(sprintf(
			'To invoke %s with custom configuration key statically 3 arguments are required: container, service name and options.',
			$factoryClassName
		));
		$tester->expectException($exception, function() use (&$factoryClassName, &$customConfigName, &$container)
		{
			$router = call_user_func([$factoryClassName, $customConfigName], $container);
		});
		$tester->expectException($exception, function() use (&$factoryClassName, &$customConfigName, &$container)
		{
			$router = call_user_func([$factoryClassName, $customConfigName], $container, 'test');
		});
	}
}