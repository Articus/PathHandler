<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Mezzio\Template\TemplateRendererInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class TemplateSpec extends ObjectBehavior
{
	public function it_renders_template_with_name_and_params_from_tuple(
		TemplateRendererInterface $renderer, StreamFactoryInterface $streamFactory, StreamInterface $stream
	)
	{
		$defaultTemplate = 'test_default_template';
		$data = ['test', ['test' => 123]];
		$out = 'test template render';

		$renderer->render($data[0], $data[1])->shouldBeCalledOnce()->willReturn($out);
		$streamFactory->createStream($out)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($streamFactory, $renderer, $defaultTemplate);
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_renders_template_with_name_from_string(
		TemplateRendererInterface $renderer, StreamFactoryInterface $streamFactory, StreamInterface $stream
	)
	{
		$defaultTemplate = 'test_default_template';
		$data = 'test';
		$out = 'test template render';

		$renderer->render($data, [])->shouldBeCalledOnce()->willReturn($out);
		$streamFactory->createStream($out)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($streamFactory, $renderer, $defaultTemplate);
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_renders_error_template_with_data_param_from_non_tuple_and_non_string(
		TemplateRendererInterface $renderer, StreamFactoryInterface $streamFactory, StreamInterface $stream
	)
	{
		$defaultTemplate = 'test_default_template';
		$data = 123;
		$out = 'test template render';

		$renderer->render($defaultTemplate, ['data' => $data])->shouldBeCalledOnce()->willReturn($out);
		$streamFactory->createStream($out)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($streamFactory, $renderer, $defaultTemplate);
		$this->assemble($data)->shouldBe($stream);
	}
}
