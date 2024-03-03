<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler\Attribute;
use Articus\PluginManager\PluginFactoryInterface;
use Closure;
use Generator;
use Psr\Container\ContainerInterface;

class IdentifiableValueListLoad implements PluginFactoryInterface
{
	protected static Closure $defaultValueReceiverFactory;

	public function __construct()
	{
		self::$defaultValueReceiverFactory = static function (): Generator
		{
			$result = [];
			while (($tuple = yield) !== null)
			{
				[$index, $id, $value] = $tuple;
				$result[$index] = $value;
			}
			return $result;
		};
	}

	public function __invoke(ContainerInterface $container, string $name, array $options = []): Attribute\IdentifiableValueListLoad
	{
		$parsedOptions = new Attribute\Options\IdentifiableValueListLoad($options);
		$valueReceiverFactory = ($parsedOptions->valueReceiverFactory === null) ? self::$defaultValueReceiverFactory : $container->get($parsedOptions->valueReceiverFactory);
		$result = new Attribute\IdentifiableValueListLoad(
			$container->get(IdentifiableValueLoader::class),
			$parsedOptions->type,
			$container->get($parsedOptions->identifierEmitter),
			$parsedOptions->identifierEmitterArgAttrs,
			$valueReceiverFactory,
			$parsedOptions->valueReceiverFactoryArgAttrs,
			$parsedOptions->valueListAttr
		);
		return $result;
	}
}
