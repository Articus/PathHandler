<?php
declare(strict_types=1);

namespace Articus\PathHandler\MetadataProvider\Factory;

use Articus\PathHandler as PH;
use Articus\PluginManager as PM;
use Psr\Container\ContainerInterface;

class PhpAttribute implements PM\ServiceFactoryInterface
{
	use PM\ConfigAwareFactoryTrait;
	use PH\CacheKeyAwareTrait;

	public function __construct(string $configKey = PH\MetadataProvider\PhpAttribute::class)
	{
		$this->configKey = $configKey;
	}

	public function __invoke(ContainerInterface $container, string $name): PH\MetadataProvider\PhpAttribute
	{
		$config = $this->getServiceConfig($container);
		$handlerPluginManager = self::getHandlerPluginManager($container);
		$cache = self::getCache($container, PH\MetadataProvider\PhpAttribute::CACHE_KEY, $config['cache'] ?? null);
		return new PH\MetadataProvider\PhpAttribute($handlerPluginManager, $cache);
	}

	protected static function getHandlerPluginManager(ContainerInterface $container): PM\PluginManagerInterface
	{
		return $container->get(PH\RouteInjectionFactory::DEFAULT_HANDLER_PLUGIN_MANAGER);
	}
}
