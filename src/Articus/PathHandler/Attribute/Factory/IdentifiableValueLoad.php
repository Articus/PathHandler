<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler\Attribute;
use Articus\PluginManager\PluginFactoryInterface;
use Psr\Container\ContainerInterface;

class IdentifiableValueLoad implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Attribute\IdentifiableValueLoad
	{
		$parsedOptions = new Attribute\Options\IdentifiableValueLoad($options);
		$result = new Attribute\IdentifiableValueLoad(
			$container->get(IdentifiableValueLoader::class),
			$parsedOptions->type,
			$parsedOptions->identifierAttr,
			$parsedOptions->valueAttr
		);
		return $result;
	}
}
