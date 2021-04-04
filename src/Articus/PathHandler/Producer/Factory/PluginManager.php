<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;

class PluginManager extends PH\ConfigAwareFactory
{
	public function __construct(string $configKey = PH\Producer\PluginManager::class)
	{
		parent::__construct($configKey);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new PH\Producer\PluginManager($container, $this->getServiceConfig($container));
	}
}
