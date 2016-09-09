<?php

namespace Articus\PathHandler\Producer;
use Articus\DataTransfer\Mapper\MapperInterface;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Articus\DataTransfer\Service as DTService;
use Zend\ServiceManager\ServiceLocatorInterface;

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
			case Template::class:
				if (!$container->has(TemplateRendererInterface::class))
				{
					throw new \LogicException('Template renderer is not configured.');
				}
				$result = new Template($container->get(TemplateRendererInterface::class));
				break;
			case Transfer::class:
				if (!$container->has(DTService::class))
				{
					throw new \LogicException('Data transfer service is not configured.');
				}
				$mapper = null;
				if (!empty($options['mapper']))
				{
					$mapper = $options['mapper'];
					switch (true)
					{
						case (is_string($mapper) && $container->has($mapper)):
							$mapper = $container->get($mapper);
							break;
						case (is_array($mapper)
							&& isset($mapper['name'], $mapper['options'])
							&& ($container instanceof ServiceLocatorInterface)
							&& $container->has($mapper['name'])
						):
							$mapper = $container->build($mapper['name'], $mapper['options']);
							break;
						case (is_callable($mapper) || ($mapper instanceof MapperInterface)):
							//Allow direct pass of object or callback
							break;
						default:
							$mapper = null;
							break;
					}
				}
				$result = new Transfer($container->get(DTService::class), $mapper);
				break;
			default:
				throw new ServiceNotCreatedException(sprintf('Unable to create producer %s.', $requestedName));

		}
		return $result;
	}

}