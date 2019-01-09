<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Router\RouteResult;

class TransferSpec extends ObjectBehavior
{
	public function it_transfers_data_from_query(DTService $dt, Request $in, Request $out, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'source' => PH\Attribute\Transfer::SOURCE_GET,
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];

		$in->getQueryParams()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($options->getObjectAttr(), $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, $object)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $options);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_data_from_parsed_body(DTService $dt, Request $in, Request $out, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'source' => PH\Attribute\Transfer::SOURCE_POST,
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($options->getObjectAttr(), $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, $object)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $options);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_data_transfer_from_invalid_parsed_body(DTService $dt, Request $in, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'source' => PH\Attribute\Transfer::SOURCE_POST,
			'type' => \stdClass::class,
		]);
		$data = 123;

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);

		$dt->transfer($data, $object)->shouldNotBeCalled();

		$this->beConstructedWith($dt, $options);
		$this->shouldThrow(PH\Exception\BadRequest::class)->during('__invoke', [$in]);
	}

	public function it_transfers_data_from_headers(DTService $dt, Request $in, Request $out, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'source' => PH\Attribute\Transfer::SOURCE_HEADER,
			'type' => \stdClass::class,
		]);
		$data = ['test1' => [123], 'test2' => [123, 456]];
		$transferData = ['test1' => 123, 'test2' => [123, 456]];

		$in->getHeaders()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($options->getObjectAttr(), $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($transferData, $object)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $options);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_data_from_routing(DTService $dt, Request $in, Request $out, RouteResult $routing, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'source' => PH\Attribute\Transfer::SOURCE_ROUTE,
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];

		$routing->getMatchedParams()->shouldBeCalledOnce()->willReturn($data);

		$in->getAttribute(RouteResult::class)->shouldBeCalledOnce()->willReturn($routing);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($options->getObjectAttr(), $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, $object)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $options);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_data_transfer_from_routing_if_there_is_no_routing_result(DTService $dt, Request $in, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'source' => PH\Attribute\Transfer::SOURCE_ROUTE,
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];

		$in->getAttribute(RouteResult::class)->shouldBeCalledOnce()->willReturn(null);

		$dt->transfer($data, $object)->shouldNotBeCalled();

		$this->beConstructedWith($dt, $options);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$in]);
	}

	public function it_transfers_data_from_attributes(DTService $dt, Request $in, Request $out, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'source' => PH\Attribute\Transfer::SOURCE_ATTRIBUTE,
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];

		$in->getAttributes()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($options->getObjectAttr(), $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, $object)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $options);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_invalid_data_source(DTService $dt, Request $in)
	{
		$options = new PH\Attribute\Options\Transfer([
			'source' => 'invalid',
			'type' => \stdClass::class,
		]);

		$this->beConstructedWith($dt, $options);
		$this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$in]);
	}

	public function it_saves_object_to_attribute_with_custom_name(DTService $dt, Request $in, Request $out, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'object_attr' => 'test',
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($options->getObjectAttr(), $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, $object)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $options);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_creates_object_if_object_attribute_is_empty(DTService $dt, Request $in, Request $out)
	{
		$options = new PH\Attribute\Options\Transfer([
			'object_attr' => 'test',
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn(null);
		$in->withAttribute($options->getObjectAttr(), Argument::type($options->getType()))->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, Argument::type($options->getType()))->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $options);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_invalid_object_type(DTService $dt, Request $in)
	{
		$options = new PH\Attribute\Options\Transfer([
			'type' => 'invalid',
		]);
		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);

		$this->beConstructedWith($dt, $options);
		$this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$in]);
	}

	public function it_throws_on_transfer_error_if_error_attribute_name_is_not_set(DTService $dt, Request $in, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'object_attr' => 'test',
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];
		$error = ['wrong' => 456];
		$exception = new PH\Exception\UnprocessableEntity($error);

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn($object);

		$dt->transfer($data, $object)->shouldBeCalledOnce()->willReturn($error);

		$this->beConstructedWith($dt, $options);
		$this->shouldThrow($exception)->during('__invoke', [$in]);
	}

	public function it_saves_transfer_error_to_error_attribute_if_its_name_is_set(DTService $dt, Request $in, Request $out, \stdClass $object)
	{
		$options = new PH\Attribute\Options\Transfer([
			'error_attr' => 'test',
			'type' => \stdClass::class,
		]);
		$data = ['test' => 123];
		$error = ['wrong' => 456];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($options->getObjectAttr())->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($options->getErrorAttr(), $error)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, $object)->shouldBeCalledOnce()->willReturn($error);

		$this->beConstructedWith($dt, $options);
		$this->__invoke($in)->shouldBe($out);
	}
}
