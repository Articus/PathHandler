<?php
declare(strict_types=1);

namespace Articus\PathHandler\MetadataProvider\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;

class PhpAttribute extends PH\ConfigAwareFactory
{
	use PH\CacheKeyAwareTrait;

	public function __construct(string $configKey = PH\MetadataProvider\PhpAttribute::class)
	{
		parent::__construct($configKey);
	}

	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$config = \array_merge($this->getServiceConfig($container), $options ?? []);
		$handlerPluginManager = self::getHandlerPluginManager($container);
		$cache = self::getCache($container, PH\MetadataProvider\PhpAttribute::CACHE_KEY, $config['cache'] ?? null);
		return new PH\MetadataProvider\PhpAttribute($handlerPluginManager, $cache);
	}

	protected static function getHandlerPluginManager(ContainerInterface $container): PH\Handler\PluginManager
	{
		return $container->get(PH\Handler\PluginManager::class);
	}
}
