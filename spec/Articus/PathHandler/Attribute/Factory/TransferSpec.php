<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\Service as DTService;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use stdClass;

class TransferSpec extends ObjectBehavior
{
	public function it_builds_transfer_attribute_with_simple_config(ContainerInterface $container, DTService $dt)
	{
		$type = stdClass::class;
		$options = [
			'type' => $type,
		];
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$this->__invoke($container, 'test', $options)->shouldHaveProperty('type', $type);
	}
}
