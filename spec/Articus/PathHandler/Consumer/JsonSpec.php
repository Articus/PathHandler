<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Consumer;

use Articus\PathHandler as PH;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;
use spec\Utility\GlobalFunctionMock;
use stdClass;
use const JSON_OBJECT_AS_ARRAY;

class JsonSpec extends ObjectBehavior
{
	public function it_parses_valid_json_null_from_body(StreamInterface $body)
	{
		$this->beConstructedWith(JSON_OBJECT_AS_ARRAY, 512);
		$data = null;
		$json = 'null';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_throws_on_valid_json_int_in_body(StreamInterface $body)
	{
		$this->beConstructedWith(JSON_OBJECT_AS_ARRAY, 512);
		$json = '123';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->shouldThrow(PH\Exception\UnprocessableEntity::class)->during('parse', [$body, null, 'mime/test', []]);
	}

	public function it_throws_on_valid_json_float_in_body(StreamInterface $body)
	{
		$this->beConstructedWith(JSON_OBJECT_AS_ARRAY, 512);
		$json = '123.456';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->shouldThrow(PH\Exception\UnprocessableEntity::class)->during('parse', [$body, null, 'mime/test', []]);
	}

	public function it_throws_on_valid_json_string_in_body(StreamInterface $body)
	{
		$this->beConstructedWith(JSON_OBJECT_AS_ARRAY, 512);
		$json = '"qwer"';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->shouldThrow(PH\Exception\UnprocessableEntity::class)->during('parse', [$body, null, 'mime/test', []]);
	}

	public function it_parses_valid_json_array_from_body(StreamInterface $body)
	{
		$this->beConstructedWith(JSON_OBJECT_AS_ARRAY, 512);
		$data = [null, 123, 123.456, 'qwer'];
		$json = '[null,123,123.456,"qwer"]';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_parses_valid_json_object_from_body_as_array(StreamInterface $body)
	{
		$this->beConstructedWith(JSON_OBJECT_AS_ARRAY, 512);
		$data = ['test' => 123];
		$json = '{"test": 123}';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->parse($body, null, 'mime/test', [])->shouldBe($data);
	}

	public function it_parses_valid_json_using_flags_and_depth(StreamInterface $body)
	{
		if (GlobalFunctionMock::disabled())
		{
			throw new SkippingException('No global function mock');
		}

		$flags = 123;
		$depth = 234;
		$data = new stdClass();
		$data->test = 123;
		$json = '{"test": 123}';

		$this->beConstructedWith($flags, $depth);
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		GlobalFunctionMock::shouldReceive('json_decode')->with($json, null, $depth, $flags)->andReturn($data);

		$this->parse($body, null, 'mime/test', [])->shouldBeLike($data);

		GlobalFunctionMock::tearDown();
	}

	public function it_throws_on_invalid_json_in_body(StreamInterface $body)
	{
		$this->beConstructedWith(JSON_OBJECT_AS_ARRAY, 512);
		$json = '{';
		$body->getContents()->shouldBeCalledOnce()->willReturn($json);
		$this->shouldThrow(PH\Exception\BadRequest::class)->during('parse', [$body, null, 'mime/test', []]);
	}
}
