<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Zend\Expressive\Response\ServerRequestErrorResponseGenerator;
use Zend\Expressive\Template\TemplateRendererInterface;

class TemplateSpec extends ObjectBehavior
{
	public function it_renders_template_with_name_and_params_from_tuple(TemplateRendererInterface $renderer)
	{
		$data = ['test', ['test' => 123]];
		$out = 'test template render';

		$renderer->render($data[0], $data[1])->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($renderer);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
		$stream = $this->assemble($data);
		$stream->getContents()->shouldBe($out);
	}

	public function it_renders_template_with_name_from_string(TemplateRendererInterface $renderer)
	{
		$data = 'test';
		$out = 'test template render';

		$renderer->render($data, [])->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($renderer);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
		$stream = $this->assemble($data);
		$stream->getContents()->shouldBe($out);
	}

	public function it_renders_error_template_with_data_param_from_non_tuple_and_non_string(TemplateRendererInterface $renderer)
	{
		$data = 123;
		$out = 'test template render';

		$renderer->render(ServerRequestErrorResponseGenerator::TEMPLATE_DEFAULT, ['data' => $data])->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($renderer);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
		$stream = $this->assemble($data);
		$stream->getContents()->shouldBe($out);
	}
}
