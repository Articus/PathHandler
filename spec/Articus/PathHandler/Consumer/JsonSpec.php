<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Consumer;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;

class JsonSpec extends ObjectBehavior
{
	public function let()
	{
		$this->shouldImplement(PH\Consumer\ConsumerInterface::class);
	}

	public function it_parses_valid_json_null_from_body(StreamInterface $body)
	{
		$data = null;
		$json = 'null';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_parses_valid_json_int_in_body(StreamInterface $body)
	{
		$data = 123;
		$json = '123';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_parses_valid_json_float_in_body(StreamInterface $body)
	{
		$data = 123.456;
		$json = '123.456';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_parses_valid_json_string_in_body(StreamInterface $body)
	{
		$data = 'qwer';
		$json = '"qwer"';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_parses_valid_json_array_from_body(StreamInterface $body)
	{
		$data = [null, 123, 123.456, 'qwer'];
		$json = '[null,123,123.456,"qwer"]';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_parses_valid_json_object_from_body(StreamInterface $body)
	{
		$data = ['test' => 123];
		$json = '{"test": 123}';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_throws_on_invalid_json_in_body(StreamInterface $body)
	{
		$json = '{';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->shouldThrow(PH\Exception\BadRequest::class)->during('parse', [$body, null, 'mime/test', []]);
	}
}
