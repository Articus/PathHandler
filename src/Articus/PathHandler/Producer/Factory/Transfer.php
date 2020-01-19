<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class Transfer implements FactoryInterface
{
	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$subset = $options['subset'] ?? '';
		return new PH\Producer\Transfer($container->get(StreamInterface::class), $container->get(DTService::class), $subset);
	}
}