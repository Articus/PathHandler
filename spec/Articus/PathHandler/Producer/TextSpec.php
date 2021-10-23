<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Articus\PathHandler as PH;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;

class TextSpec extends ObjectBehavior
{
	public function let(StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream->getWrappedObject();
		};
		$this->beConstructedWith($streamFactory);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
	}

	public function it_produces_empty_string_from_null_data(StreamInterface $stream)
	{
		$stream->write('')->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();
		$this->assemble(null)->shouldBe($stream);
	}

	public function it_produces_string_from_string(StreamInterface $stream)
	{
		$data = 'test123';
		$stream->write($data)->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_produces_string_from_stringable_object(StreamInterface $stream)
	{
		if (\PHP_MAJOR_VERSION < 8)
		{
			throw new SkippingException('PHP 8+ is required');
		}
		$data = new class () implements \Stringable
		{
			public function __toString()
			{
				return 'test123';
			}
		};
		$stream->write($data->__toString())->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_produces_string_from_object_with_magic_method(StreamInterface $stream)
	{
		$data = new class ()
		{
			public function __toString()
			{
				return 'test123';
			}
		};
		$stream->write($data->__toString())->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_throws_on_bool()
	{
		$data = true;
		$this->shouldThrow(\InvalidArgumentException::class)->during('assemble', [$data]);
	}

	public function it_throws_on_int()
	{
		$data = 123;
		$this->shouldThrow(\InvalidArgumentException::class)->during('assemble', [$data]);
	}

	public function it_throws_on_float()
	{
		$data = 123.456;
		$this->shouldThrow(\InvalidArgumentException::class)->during('assemble', [$data]);
	}

	public function it_throws_on_array()
	{
		$data = [123];
		$this->shouldThrow(\InvalidArgumentException::class)->during('assemble', [$data]);
	}
}
