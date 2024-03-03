<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\PathHandler\Producer;
use Articus\PluginManager\PluginFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Json implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Producer\Json
	{
		$parsedOptions = new Producer\Options\Json($options);
		return new Producer\Json(
			$container->get(StreamFactoryInterface::class),
			$parsedOptions->encodeFlags,
			$parsedOptions->depth
		);
	}
}
