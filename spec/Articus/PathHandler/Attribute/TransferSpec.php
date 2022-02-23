<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute;

use spec\Example\InstanciatorInterface as Invokable;
use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface as Request;
use Mezzio\Router\RouteResult;

class TransferSpec extends ObjectBehavior
{
	public function it_transfers_data_from_query(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_GET;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getQueryParams()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_data_from_parsed_body(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_data_from_headers(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_HEADER;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test1' => [123], 'test2' => [123, 456]];
		$transferData = ['test1' => 123, 'test2' => [123, 456]];

		$in->getHeaders()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($transferData, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_data_from_routing(DTService $dt, Request $in, Request $out, Invokable $instanciator, RouteResult $routing, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_ROUTE;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$routing->getMatchedParams()->shouldBeCalledOnce()->willReturn($data);

		$in->getAttribute(RouteResult::class)->shouldBeCalledOnce()->willReturn($routing);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_data_transfer_from_routing_if_there_is_no_routing_result(DTService $dt, Request $in, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_ROUTE;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getAttribute(RouteResult::class)->shouldBeCalledOnce()->willReturn(null);

		$dt->transferToTypedData($data, $object, $subset)->shouldNotBeCalled();

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$in]);
	}

	public function it_transfers_data_from_attributes(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_ATTRIBUTE;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getAttributes()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_invalid_data_source(DTService $dt, Request $in, Invokable $instanciator)
	{
		$source = 'invalid';
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$in]);
	}

	public function it_transfers_custom_subset_of_data(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$type = \stdClass::class;
		$subset = 'testSubset';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_saves_object_to_attribute_with_custom_name(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'test';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_creates_object_with_instanciator_passing_request_if_object_attribute_is_empty(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn(null);
		$instanciator->__invoke($type, $in)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_creates_object_with_instanciator_passing_specified_attributes_if_object_attribute_is_empty(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = ['test1', 'test2'];
		$errorAttr = null;

		$data = ['test' => 123];
		$instanciatorArgAttrValues = ['value1', 'value2'];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn(null);
		$in->getAttribute($instanciatorArgAttrs[0])->shouldBeCalledOnce()->willReturn($instanciatorArgAttrValues[0]);
		$in->getAttribute($instanciatorArgAttrs[1])->shouldBeCalledOnce()->willReturn($instanciatorArgAttrValues[1]);
		$instanciator->__invoke($type, ...$instanciatorArgAttrValues)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_invalid_object_type(DTService $dt, Request $in, Invokable $instanciator)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$type = 'invalid';
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->shouldThrow(\InvalidArgumentException::class)->during('__invoke', [$in]);
	}

	public function it_throws_on_transfer_error_if_error_attribute_name_is_not_set(DTService $dt, Request $in, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = null;

		$data = ['test' => 123];
		$error = ['wrong' => 456];
		$exception = new PH\Exception\UnprocessableEntity($error);

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);

		$dt->transferToTypedData($data, $object, $subset)->shouldBeCalledOnce()->willReturn($error);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->shouldThrow($exception)->during('__invoke', [$in]);
	}

	public function it_saves_transfer_error_to_error_attribute_if_its_name_is_set(DTService $dt, Request $in, Request $out, Invokable $instanciator, \stdClass $object)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$type = \stdClass::class;
		$subset = '';
		$objectAttr = 'object';
		$instanciatorArgAttrs = [];
		$errorAttr = 'test';

		$data = ['test' => 123];
		$error = ['wrong' => 456];

		$in->getParsedBody()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($errorAttr, $error)->shouldBeCalledOnce()->willReturn($out);

		$dt->transferToTypedData($data, $object, '')->shouldBeCalledOnce()->willReturn($error);

		$this->beConstructedWith($dt, $source, $type, $subset, $objectAttr, $instanciator, $instanciatorArgAttrs, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}
}
