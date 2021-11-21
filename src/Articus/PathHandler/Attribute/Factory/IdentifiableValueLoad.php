<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IdentifiableValueLoad implements FactoryInterface
{
	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$options = new PH\Attribute\Options\IdentifiableValueLoad($options);
		$result = new PH\Attribute\IdentifiableValueLoad(
			$container->get(IdentifiableValueLoader::class),
			$options->type,
			$options->identifierAttr,
			$options->valueAttr
		);
		return $result;
	}
}
