<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class Text implements FactoryInterface
{
	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new PH\Producer\Text($container->get(StreamInterface::class));
	}
}
