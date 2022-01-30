<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IdentifiableValueListLoad implements FactoryInterface
{
	/**
	 * @var callable
	 */
	protected static $defaultValueReceiverFactory;

	public function __construct()
	{
		self::$defaultValueReceiverFactory = static function ()
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

	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$options = new PH\Attribute\Options\IdentifiableValueListLoad($options);
		$valueReceiverFactory = ($options->valueReceiverFactory === null) ? self::$defaultValueReceiverFactory : $container->get($options->valueReceiverFactory);
		$result = new PH\Attribute\IdentifiableValueListLoad(
			$container->get(IdentifiableValueLoader::class),
			$options->type,
			$container->get($options->identifierEmitter),
			$options->identifierEmitterArgAttrs,
			$valueReceiverFactory,
			$options->valueReceiverFactoryArgAttrs,
			$options->valueListAttr
		);
		return $result;
	}
}
