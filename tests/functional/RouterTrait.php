<?php
namespace Test\PathHandler;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;

trait RouterTrait
{
	/**
	 * @param string $serviceName
	 * @param array $routes
	 * @return PH\Router\TreeConfiguration
	 */
	protected function createRouter($serviceName, array $routes)
	{
		$containerProphecy = $this->prophesize(ContainerInterface::class);
		$config = [
			'router_config' => [
				'routes' => $routes,
			],
		];
		$containerProphecy->get('config')->willReturn($config);
		$factory = new PH\Router\Factory\TreeConfiguration('router_config');
		return $factory($containerProphecy->reveal(), $serviceName);
	}

}