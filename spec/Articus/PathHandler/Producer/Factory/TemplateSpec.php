<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Zend\Expressive\Template\TemplateRendererInterface;

class TemplateSpec extends ObjectBehavior
{
	public function it_builds_template_producer(ContainerInterface $container, TemplateRendererInterface $renderer)
	{
		$options = [];
		$container->get(TemplateRendererInterface::class)->shouldBeCalledOnce()->willReturn($renderer);
		$this->__invoke($container, 'test', $options)->shouldBeAnInstanceOf(PH\Producer\Template::class);
	}
}

