<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;

class PluginManager extends PH\ConfigAwareFactory
{
	public function __construct(string $configKey = PH\Attribute\PluginManager::class)
	{
		parent::__construct($configKey);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new PH\Attribute\PluginManager($container, $this->getServiceConfig($container));
	}
}
