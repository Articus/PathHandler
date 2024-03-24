<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer as DT;
use Articus\PathHandler\Attribute;
use Articus\PluginManager\PluginFactoryInterface;
use Articus\PluginManager\PluginManagerInterface;
use Psr\Container\ContainerInterface;

class AnonymousTransfer implements PluginFactoryInterface
{
	public function __invoke(ContainerInterface $container, string $name, array $options = []): Attribute\AnonymousTransfer
	{
		$parsedOptions = new Attribute\Options\AnonymousTransfer($options);
		$strategy = self::getStrategyManager($container)(...$parsedOptions->strategy);
		$validator = self::getValidatorManager($container)(...$parsedOptions->validator);
		$result = new Attribute\AnonymousTransfer(
			$container->get(DT\Service::class),
			$parsedOptions->source,
			$strategy,
			$validator,
			$parsedOptions->objectAttr,
			$parsedOptions->errorAttr
		);
		return $result;
	}

	protected static function getStrategyManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER);
	}

	protected static function getValidatorManager(ContainerInterface $container): PluginManagerInterface
	{
		return $container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER);
	}
}
