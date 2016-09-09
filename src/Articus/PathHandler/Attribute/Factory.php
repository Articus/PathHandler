<?php

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\Service as DTService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;

class Factory implements FactoryInterface
{
	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$result = null;
		switch ($requestedName)
		{
			case Transfer::class:
				$options = new Options\Transfer($options);
				$result = new Transfer($container->get(DTService::class), $options);
				break;
			default:
				throw new ServiceNotCreatedException(sprintf('Unable to create attribute %s.', $requestedName));
		}
		return $result;
	}

}