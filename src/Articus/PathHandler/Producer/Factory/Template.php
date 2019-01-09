<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class Template implements FactoryInterface
{
	/**
	 * @inheritdoc
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new PH\Producer\Template($container->get(TemplateRendererInterface::class));
	}
}