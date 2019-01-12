<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;

class JsonSpec extends ObjectBehavior
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

	public function it_produces_null_from_null_data()
	{
		$this->assemble(null)->shouldBe(null);
	}

	public function it_produces_json_from_json_serializable_data(StreamInterface $stream)
	{
		$data = ['test' => 123];
		$stream->write(\json_encode($data))->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();
		$this->assemble($data)->shouldBe($stream);
	}

	public function it_throws_on_non_json_serializable_data()
	{
		$data = "\xB1\x31";
		$this->shouldThrow(\InvalidArgumentException::class)->during('assemble', [$data]);
	}
}
