<?php
namespace Test\PathHandler;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Test\PathHandler\Sample\Handler;
use Zend\Cache\Storage\StorageInterface;
use Zend\Expressive\Router\RouterInterface;

class MiddlewareFactoryTest extends \Codeception\Test\Unit
{
	/**
	 * @var \Test\PathHandler\FunctionalTester
	 */
	protected $tester;

	public function testServiceIsCreatedFromSimpleConfiguration()
	{
		$config = [
			'path_handler' => [
				'routes' => [
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
				],
				'handlers' => [
					'invokables' => [
						'Handler' => 'My\Handler',
					],
				],
				'metadata_cache' => [
					'adapter' => 'blackhole',
				],
			],
		];
		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$containerProphecy->get('config')->willReturn($config);
		$container = $containerProphecy->reveal();

		$factory = new PH\MiddlewareFactory();
		$service = $factory($container, PH\Middleware::class);
		$this->tester->assertInstanceOf(PH\Middleware::class, $service);
	}

	public function testServiceIsCreatedFromExternalConfiguration()
	{
		$config = [
			'path_handler' => [
				'handler_attr' => 'custom_name',
				'routes' => 'Router',
				'handlers' => 'HandlerPluginManager',
				'metadata_cache' => 'MetadataCacheStorage',
				'consumers' => 'ConsumerPluginManager',
				'attributes' => 'AttributePluginManager',
				'producers' => 'ProducerPluginManager',
			],
		];
		$containerProphecy = $this->prophesize(ContainerInterface::class);

		$containerProphecy->get('config')->willReturn($config);

		$router = $this->prophesize(RouterInterface::class)->reveal();
		$containerProphecy->get('Router')->willReturn($router);
		$containerProphecy->has('Router')->willReturn(true);

		$handlerPluginManager = $this->prophesize(PH\PluginManager::class)->reveal();
		$containerProphecy->get('HandlerPluginManager')->willReturn($handlerPluginManager);
		$containerProphecy->has('HandlerPluginManager')->willReturn(true);

		$metadataCacheStorage = $this->prophesize(StorageInterface::class)->reveal();
		$containerProphecy->get('MetadataCacheStorage')->willReturn($metadataCacheStorage);
		$containerProphecy->has('MetadataCacheStorage')->willReturn(true);

		$consumerPluginManager = $this->prophesize(PH\Consumer\PluginManager::class)->reveal();
		$containerProphecy->get('ConsumerPluginManager')->willReturn($consumerPluginManager);
		$containerProphecy->has('ConsumerPluginManager')->willReturn(true);

		$attributePluginManager = $this->prophesize(PH\Attribute\PluginManager::class)->reveal();
		$containerProphecy->get('AttributePluginManager')->willReturn($attributePluginManager);
		$containerProphecy->has('AttributePluginManager')->willReturn(true);

		$producerPluginManager = $this->prophesize(PH\Producer\PluginManager::class)->reveal();
		$containerProphecy->get('ProducerPluginManager')->willReturn($producerPluginManager);
		$containerProphecy->has('ProducerPluginManager')->willReturn(true);
		
		$container = $containerProphecy->reveal();

		$factory = new PH\MiddlewareFactory();
		/** @var PH\Middleware $middleware */
		$middleware = $factory($container, PH\Middleware::class);
		$this->tester->assertInstanceOf(PH\Middleware::class, $middleware);
		$this->tester->assertEquals('custom_name', $middleware->getHandlerAttr());
		$this->tester->assertSame($router, $middleware->getRouter());
		$this->tester->assertSame($metadataCacheStorage, $middleware->getMetadataCacheStorage());
		$this->tester->assertSame($consumerPluginManager, $middleware->getConsumerPluginManager());
		$this->tester->assertSame($attributePluginManager, $middleware->getAttributePluginManager());
		$this->tester->assertSame($producerPluginManager, $middleware->getProducerPluginManager());
	}
}