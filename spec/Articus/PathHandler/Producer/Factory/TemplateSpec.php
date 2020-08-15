<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;
use Mezzio\Template\TemplateRendererInterface;

class TemplateSpec extends ObjectBehavior
{
	public function it_builds_template_producer(ContainerInterface $container, TemplateRendererInterface $renderer, StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};
		$container->get(TemplateRendererInterface::class)->shouldBeCalledOnce()->willReturn($renderer);
		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$this->__invoke($container, 'test', [])->shouldBeAnInstanceOf(PH\Producer\Template::class);
	}
}

