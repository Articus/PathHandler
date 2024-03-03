<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\PathHandler\Producer;
use Articus\PluginManager\PluginFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Text implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Producer\Text
	{
		return new Producer\Text($container->get(StreamFactoryInterface::class));
	}
}
