<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler\Attribute;
use Articus\PluginManager\PluginFactoryInterface;
use Closure;
use Psr\Container\ContainerInterface;

class Transfer implements PluginFactoryInterface
{
	protected static Closure $defaultInstanciator;

	public function __construct()
	{
		self::$defaultInstanciator = static fn (string $type): object => new $type();
	}

	public function __invoke(ContainerInterface $container, string $name, array $options = []): Attribute\Transfer
	{
		$parsedOptions = new Attribute\Options\Transfer($options);
		$instanciator = ($parsedOptions->instanciator === null) ? self::$defaultInstanciator : $container->get($parsedOptions->instanciator);
		$result = new Attribute\Transfer(
			$container->get(DTService::class),
			$parsedOptions->source,
			$parsedOptions->type,
			$parsedOptions->subset,
			$parsedOptions->objectAttr,
			$instanciator,
			$parsedOptions->instanciatorArgAttrs,
			$parsedOptions->errorAttr
		);
		return $result;
	}
}
