<?php
declare(strict_types=1);

namespace Articus\PathHandler;

use Interop\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

trait CacheKeyAwareTrait
{
	protected static function getCache(ContainerInterface $container, string $cacheKey, $options): CacheInterface
	{
		$result = null;
		switch (true)
		{
			case ($options === null):
			case \is_array($options):
				$result = new Cache\DataFilePerKey($cacheKey, $options['directory'] ?? null);
				break;
			case (\is_string($options) && $container->has($options)):
				$result = $container->get($options);
				if (!($result instanceof CacheInterface))
				{
					throw new \LogicException(\sprintf('Invalid cache service for key "%s".', $cacheKey));
				}
				break;
			default:
				throw new \LogicException(\sprintf('Invalid cache configuration for key "%s".', $cacheKey));
		}
		return $result;
	}

}
