<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\PathHandler\Producer;
use Articus\PluginManager\PluginFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Template implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Producer\Template
	{
		$parsedOptions = new Producer\Options\Template($options);
		return new Producer\Template(
			$container->get(StreamFactoryInterface::class),
			$container->get($parsedOptions->templateRendererServiceName),
			$parsedOptions->defaultTemplate
		);
	}
}
