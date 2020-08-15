<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;
use Mezzio\Response\ServerRequestErrorResponseGenerator;
use Mezzio\Template\TemplateRendererInterface;

class TemplateSpec extends ObjectBehavior
{
	public function it_renders_template_with_name_and_params_from_tuple(TemplateRendererInterface $renderer, StreamInterface $stream)
	{
		$data = ['test', ['test' => 123]];
		$out = 'test template render';

		$streamFactory = function () use ($stream)
		{
			return $stream->getWrappedObject();
		};
		$stream->write($out)->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();

		$renderer->render($data[0], $data[1])->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($streamFactory, $renderer);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_renders_template_with_name_from_string(TemplateRendererInterface $renderer, StreamInterface $stream)
	{
		$data = 'test';
		$out = 'test template render';

		$streamFactory = function () use ($stream)
		{
			return $stream->getWrappedObject();
		};
		$stream->write($out)->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();

		$renderer->render($data, [])->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($streamFactory, $renderer);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_renders_error_template_with_data_param_from_non_tuple_and_non_string(TemplateRendererInterface $renderer, StreamInterface $stream)
	{
		$data = 123;
		$out = 'test template render';

		$streamFactory = function () use ($stream)
		{
			return $stream->getWrappedObject();
		};
		$stream->write($out)->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();

		$renderer->render(ServerRequestErrorResponseGenerator::TEMPLATE_DEFAULT, ['data' => $data])->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($streamFactory, $renderer);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
		$this->assemble($data)->shouldBe($stream);
	}
}
