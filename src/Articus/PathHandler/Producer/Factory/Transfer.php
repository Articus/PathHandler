<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\DataTransfer\Mapper\MapperInterface;
use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Transfer implements FactoryInterface
{
	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$mapper = null;
		if (!empty($options['mapper']))
		{
			$mapperConfig = $options['mapper'];
			switch (true)
			{
				case (\is_string($mapperConfig) && $container->has($mapperConfig)):
					$mapper = $container->get($mapperConfig);
					if (!self::isMapper($mapper))
					{
						throw new \LogicException(\sprintf('Invalid mapper %s.', $mapperConfig));
					}
					break;
				case (\is_array($mapperConfig)
					&& isset($mapperConfig['name'], $mapperConfig['options'])
					&& ($container instanceof ServiceLocatorInterface)
					&& $container->has($mapperConfig['name'])
				):
					$mapper = $container->build($mapperConfig['name'], $mapperConfig['options']);
					if (!self::isMapper($mapper))
					{
						throw new \LogicException(\sprintf('Invalid mapper %s.', $mapperConfig['name']));
					}
					break;
				case (self::isMapper($mapperConfig)):
					//Allow direct pass of object or callback
					$mapper = $mapperConfig;
					break;
				default:
					throw new \LogicException('Invalid mapper.');
			}
		}
		return new PH\Producer\Transfer($container->get(StreamInterface::class), $container->get(DTService::class), $mapper);
	}

	/**
	 * @param mixed $mapper
	 * @return bool
	 */
	protected static function isMapper($mapper): bool
	{
		return (\is_callable($mapper) || ($mapper instanceof MapperInterface));
	}
}