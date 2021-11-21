<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

class IdentifiableValueLoadSpec extends ObjectBehavior
{
	public function it_builds_attribute_with_simple_config(ContainerInterface $container, IdentifiableValueLoader $loader)
	{
		$options = [
			'type' => 'test_type',
		];
		$container->get(IdentifiableValueLoader::class)->shouldBeCalledOnce()->willReturn($loader);
		$this->__invoke($container, 'test', $options)->shouldBeAnInstanceOf(PH\Attribute\IdentifiableValueLoad::class);
	}
}
