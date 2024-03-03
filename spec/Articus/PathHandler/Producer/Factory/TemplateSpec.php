<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use Mezzio\Template\TemplateRendererInterface;
use PhpSpec\ObjectBehavior;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;

class TemplateSpec extends ObjectBehavior
{
	public function it_builds_template_producer(ContainerInterface $container, TemplateRendererInterface $renderer, StreamFactoryInterface $streamFactory)
	{
		$rendererName = 'test_renderer';
		$defaultTemplate = 'test_default_template';
		$options = ['renderer' => $rendererName, 'default_template' => $defaultTemplate];

		$container->get(StreamFactoryInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$container->get($rendererName)->shouldBeCalledOnce()->willReturn($renderer);

		$service = $this->__invoke($container, 'test', $options);
		$service->shouldHaveProperty('streamFactory', $streamFactory);
		$service->shouldHaveProperty('renderer', $renderer);
		$service->shouldHaveProperty('defaultTemplate', $defaultTemplate);
	}
}
