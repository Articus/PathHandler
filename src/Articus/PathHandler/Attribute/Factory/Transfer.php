<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class Transfer implements FactoryInterface
{
	/**
	 * @var callable
	 */
	protected static $defaultInstanciator;

	public function __construct()
	{
		self::$defaultInstanciator = static function (string $type)
		{
			return new $type();
		};
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$options = new PH\Attribute\Options\Transfer($options);
		$instanciator = ($options->instanciator === null) ? self::$defaultInstanciator : $container->get($options->instanciator);
		$result = new PH\Attribute\Transfer(
			$container->get(DTService::class),
			$options->source,
			$options->type,
			$options->subset,
			$options->objectAttr,
			$instanciator,
			$options->instanciatorArgAttrs,
			$options->errorAttr
		);
		return $result;
	}
}