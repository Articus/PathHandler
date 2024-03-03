<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use Articus\PluginManager as PM;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class TransferSpec extends ObjectBehavior
{
	public function it_builds_transfer_producer(
		ContainerInterface $container, DTService $dt, PM\PluginManagerInterface $producerManager, PH\Producer\ProducerInterface $producer
	)
	{
		$subset = 'test_subset';
		$producerName = 'test_producer';
		$producerOptions = ['test' => 123];
		$options =[
			'subset' => $subset,
			'name' => $producerName,
			'options' => $producerOptions,
		];

		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$container->get(PH\RouteInjectionFactory::DEFAULT_PRODUCER_PLUGIN_MANAGER)->shouldBeCalledOnce()->willReturn($producerManager);
		$producerManager->__invoke($producerName, $producerOptions)->shouldBeCalledOnce()->willReturn($producer);

		$service = $this->__invoke($container, 'test', $options);
		$service->shouldHaveProperty('dt', $dt);
		$service->shouldHaveProperty('producer', $producer);
		$service->shouldHaveProperty('subset', $subset);
	}
}
