<?php
namespace Articus\PathHandler\Router\Factory;

use Articus\PathHandler\ConfigAwareFactory;
use Articus\PathHandler\Router;
use Interop\Container\ContainerInterface;
use Zend\Router\Http\TreeRouteStack;

class TreeConfiguration extends ConfigAwareFactory
{
	public function __construct($configKey = Router\TreeConfiguration::class)
	{
		parent::__construct($configKey);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$treeRouter = TreeRouteStack::factory($this->getServiceConfig($container));
		return new Router\TreeConfiguration($treeRouter);
	}
}