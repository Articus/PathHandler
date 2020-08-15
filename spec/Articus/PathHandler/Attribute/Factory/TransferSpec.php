<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

class TransferSpec extends ObjectBehavior
{
	public function it_builds_transfer_attribute_with_simple_config(ContainerInterface $container, DTService $dt)
	{
		$options = [
			'type' => \stdClass::class,
		];
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$this->__invoke($container, 'test', $options)->shouldBeAnInstanceOf(PH\Attribute\Transfer::class);
	}
}
