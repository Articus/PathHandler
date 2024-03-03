<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;

class IdentifiableValueLoadSpec extends ObjectBehavior
{
	public function it_builds_attribute_with_simple_config(ContainerInterface $container, IdentifiableValueLoader $loader)
	{
		$type = 'test_type';
		$options = [
			'type' => $type,
		];
		$container->get(IdentifiableValueLoader::class)->shouldBeCalledOnce()->willReturn($loader);

		$service = $this->__invoke($container, 'test', $options);
		$service->shouldHaveProperty('type', $type);
	}
}
