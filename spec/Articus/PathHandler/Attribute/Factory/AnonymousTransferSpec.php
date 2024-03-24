<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer as DT;
use Articus\PluginManager\PluginManagerInterface;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class AnonymousTransferSpec extends ObjectBehavior
{
	public function it_builds_transfer_attribute_with_simple_config(
		ContainerInterface $container,
		DT\Service $dt,
		PluginManagerInterface $strategyManager,
		PluginManagerInterface $validatorManager,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator
	)
	{
		$strategyName = 'test_strategy_name';
		$strategyOptions = ['test_strategy_option' => 111];
		$validatorName = 'test_validator_name';
		$validatorOptions = ['test_validator_option' => 222];
		$options = [
			'strategy' => [$strategyName, $strategyOptions],
			'validator' => [$validatorName, $validatorOptions],
		];
		$container->get(DT\Service::class)->shouldBeCalledOnce()->willReturn($dt);
		$container->get(DT\Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($strategyManager);
		$container->get(DT\Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($validatorManager);
		$strategyManager->__invoke($strategyName, $strategyOptions)->shouldBeCalledOnce()->willReturn($strategy);
		$validatorManager->__invoke($validatorName, $validatorOptions)->shouldBeCalledOnce()->willReturn($validator);
		$attribute = $this->__invoke($container, 'test', $options);
		$attribute->shouldHaveProperty('strategy', $strategy);
		$attribute->shouldHaveProperty('validator', $validator);
	}
}
