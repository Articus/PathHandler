<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use InvalidArgumentException;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use spec\Utility\GlobalFunctionMock;
use stdClass;
use const JSON_UNESCAPED_UNICODE;

class JsonSpec extends ObjectBehavior
{
	public function it_produces_json_with_flags_and_depth(StreamInterface $stream, StreamFactoryInterface $streamFactory)
	{
		if (GlobalFunctionMock::disabled())
		{
			throw new SkippingException('No global function mock');
		}

		$flags = 123;
		$depth = 234;
		$data = new stdClass();
		$content = 'test_string';

		GlobalFunctionMock::shouldReceive('json_encode')->with($data, $flags, $depth)->andReturn($content);
		$streamFactory->createStream($content)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($streamFactory, $flags, $depth);
		$this->assemble($data)->shouldBe($stream);

		GlobalFunctionMock::tearDown();
	}

	public function it_throws_on_non_json_serializable_data(StreamFactoryInterface $streamFactory)
	{
		$data = "\xB1\x31";
		$this->beConstructedWith($streamFactory, JSON_UNESCAPED_UNICODE, 512);
		$this->shouldThrow(InvalidArgumentException::class)->during('assemble', [$data]);
	}
}
