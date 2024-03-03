<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;

class TextSpec extends ObjectBehavior
{
	public function it_produces_empty_string_from_null_data(StreamFactoryInterface $streamFactory, StreamInterface $stream)
	{
		$streamFactory->createStream('')->shouldBeCalledOnce()->willReturn($stream);
		$this->beConstructedWith($streamFactory);
		$this->assemble(null)->shouldBe($stream);
	}

	public function it_produces_string_from_string(StreamFactoryInterface $streamFactory, StreamInterface $stream)
	{
		$data = 'test123';
		$streamFactory->createStream($data)->shouldBeCalledOnce()->willReturn($stream);
		$this->beConstructedWith($streamFactory);
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_produces_string_from_stringable_object(StreamFactoryInterface $streamFactory, StreamInterface $stream)
	{
		$data = new class () implements Stringable
		{
			public function __toString(): string
			{
				return 'test123';
			}
		};
		$streamFactory->createStream($data->__toString())->shouldBeCalledOnce()->willReturn($stream);
		$this->beConstructedWith($streamFactory);
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_produces_string_from_object_with_magic_method(StreamFactoryInterface $streamFactory, StreamInterface $stream)
	{
		$data = new class ()
		{
			public function __toString()
			{
				return 'test123';
			}
		};
		$streamFactory->createStream($data->__toString())->shouldBeCalledOnce()->willReturn($stream);
		$this->beConstructedWith($streamFactory);
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_throws_on_bool(StreamFactoryInterface $streamFactory)
	{
		$data = true;
		$this->beConstructedWith($streamFactory);
		$this->shouldThrow(InvalidArgumentException::class)->during('assemble', [$data]);
	}

	public function it_throws_on_int(StreamFactoryInterface $streamFactory)
	{
		$data = 123;
		$this->beConstructedWith($streamFactory);
		$this->shouldThrow(InvalidArgumentException::class)->during('assemble', [$data]);
	}

	public function it_throws_on_float(StreamFactoryInterface $streamFactory)
	{
		$data = 123.456;
		$this->beConstructedWith($streamFactory);
		$this->shouldThrow(InvalidArgumentException::class)->during('assemble', [$data]);
	}

	public function it_throws_on_array(StreamFactoryInterface $streamFactory)
	{
		$data = [123];
		$this->beConstructedWith($streamFactory);
		$this->shouldThrow(InvalidArgumentException::class)->during('assemble', [$data]);
	}
}
