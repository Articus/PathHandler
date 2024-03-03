<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer\Factory;

use Articus\PathHandler\Consumer;
use Articus\PluginManager\PluginFactoryInterface;
use Psr\Container\ContainerInterface;

class Json implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Consumer\Json
	{
		$parsedOptions = new Consumer\Options\Json($options);
		return new Consumer\Json($parsedOptions->decodeFlags, $parsedOptions->depth);
	}
}
