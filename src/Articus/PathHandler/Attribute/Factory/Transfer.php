<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class Transfer implements FactoryInterface
{
	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$options = new PH\Attribute\Options\Transfer($options);
		return new PH\Attribute\Transfer($container->get(DTService::class), $options);
	}
}