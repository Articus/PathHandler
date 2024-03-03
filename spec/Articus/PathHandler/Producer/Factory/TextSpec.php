<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;

class TextSpec extends ObjectBehavior
{
	public function it_builds_text_producer(ContainerInterface $container, StreamFactoryInterface $streamFactory)
	{
		$container->get(StreamFactoryInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$service = $this->__invoke($container, 'test');
		$service->shouldHaveProperty('streamFactory', $streamFactory);
	}
}
