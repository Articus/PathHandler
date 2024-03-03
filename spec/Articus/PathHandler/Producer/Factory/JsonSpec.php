<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;

class JsonSpec extends ObjectBehavior
{
	public function it_builds_json_producer(ContainerInterface $container, StreamFactoryInterface $streamFactory)
	{
		$flags = 123;
		$depth = 234;
		$options = [
			'flags' => $flags,
			'depth' => $depth,
		];
		$container->get(StreamFactoryInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);

		$service = $this->__invoke($container, 'test', $options);
		$service->shouldHaveProperty('streamFactory', $streamFactory);
		$service->shouldHaveProperty('encodeFlags', $flags);
		$service->shouldHaveProperty('depth', $depth);
	}
}
