<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Consumer\Factory;

use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use const JSON_OBJECT_AS_ARRAY;

class JsonSpec extends ObjectBehavior
{
	public function it_builds_json_consumer_with_default_options(ContainerInterface $container)
	{
		$service = $this->__invoke($container, 'test');
		$service->shouldHaveProperty('decodeFlags', JSON_OBJECT_AS_ARRAY);
		$service->shouldHaveProperty('depth', 512);
	}

	public function it_builds_json_consumer_with_specified_options(ContainerInterface $container)
	{
		$flags = 0;
		$depth = 123;
		$service = $this->__invoke($container, 'test', ['flags' => $flags, 'depth' => $depth]);
		$service->shouldHaveProperty('decodeFlags', $flags);
		$service->shouldHaveProperty('depth', $depth);
	}
}
