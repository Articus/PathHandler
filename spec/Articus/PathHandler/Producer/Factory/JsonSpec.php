<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;

class JsonSpec extends ObjectBehavior
{
	public function it_builds_json_producer(ContainerInterface $container, StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};

		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$this->__invoke($container, 'test', [])->shouldBeAnInstanceOf(PH\Producer\Json::class);
	}
}
