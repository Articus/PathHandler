<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class Json implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$parseAsStdClass = $options['parse_as_std_class'] ?? false;
		return new PH\Consumer\Json($parseAsStdClass);
	}
}
