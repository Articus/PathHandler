<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute;

use Articus\DataTransfer as DT;
use Articus\PathHandler as PH;
use LogicException;
use Mezzio\Router\RouteResult;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface as Request;
use stdClass;

class AnonymousTransferSpec extends ObjectBehavior
{
	public function it_transfers_data_from_query(
		DT\Service $dt,
		Request $in,
		Request $out,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator,
		stdClass $object
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_GET;
		$objectAttr = 'object';
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getQueryParams()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_data_from_parsed_body(
		DT\Service $dt,
		Request $in,
		Request $out,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator,
		stdClass $object
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$objectAttr = 'object';
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getAttribute(PH\Middleware::PARSED_BODY_ATTR_NAME)->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_data_from_headers(
		DT\Service $dt,
		Request $in,
		Request $out,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator,
		stdClass $object
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_HEADER;
		$objectAttr = 'object';
		$errorAttr = null;

		$data = ['test1' => [123], 'test2' => [123, 456]];

		$in->getHeaders()->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_data_from_routing(
		DT\Service $dt,
		Request $in,
		Request $out,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator,
		RouteResult $routing,
		stdClass $object
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_ROUTE;
		$objectAttr = 'object';
		$errorAttr = null;

		$data = ['test' => 123];

		$routing->getMatchedParams()->shouldBeCalledOnce()->willReturn($data);

		$in->getAttribute(RouteResult::class)->shouldBeCalledOnce()->willReturn($routing);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_data_transfer_from_routing_if_there_is_no_routing_result(
		DT\Service $dt,
		Request $in,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator,
		stdClass $object
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_ROUTE;
		$objectAttr = 'object';
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getAttribute(RouteResult::class)->shouldBeCalledOnce()->willReturn(null);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldNotBeCalled();

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->shouldThrow(LogicException::class)->during('__invoke', [$in]);
	}

	public function it_saves_object_to_attribute_with_custom_name(
		DT\Service $dt,
		Request $in,
		Request $out,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator,
		stdClass $object
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$objectAttr = 'test';
		$errorAttr = null;

		$data = ['test' => 123];

		$in->getAttribute(PH\Middleware::PARSED_BODY_ATTR_NAME)->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_transfers_to_null_if_object_attribute_is_empty(
		DT\Service $dt,
		Request $in,
		Request $out,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$objectAttr = 'object';
		$errorAttr = null;

		$data = ['test' => 123];
		$object = null;

		$in->getAttribute(PH\Middleware::PARSED_BODY_ATTR_NAME)->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn(null);
		$in->withAttribute($objectAttr, $object)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldBeCalledOnce()->willReturn([]);

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_on_transfer_error_if_error_attribute_name_is_not_set(
		DT\Service $dt,
		Request $in,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator,
		stdClass $object
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$objectAttr = 'object';
		$errorAttr = null;

		$data = ['test' => 123];
		$error = ['wrong' => 456];
		$exception = new PH\Exception\UnprocessableEntity($error);

		$in->getAttribute(PH\Middleware::PARSED_BODY_ATTR_NAME)->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldBeCalledOnce()->willReturn($error);

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->shouldThrow($exception)->during('__invoke', [$in]);
	}

	public function it_saves_transfer_error_to_error_attribute_if_its_name_is_set(
		DT\Service $dt,
		Request $in,
		Request $out,
		DT\Strategy\StrategyInterface $strategy,
		DT\Validator\ValidatorInterface $validator,
		stdClass $object
	)
	{
		$source = PH\Attribute\Transfer::SOURCE_POST;
		$objectAttr = 'object';
		$errorAttr = 'test';

		$data = ['test' => 123];
		$error = ['wrong' => 456];

		$in->getAttribute(PH\Middleware::PARSED_BODY_ATTR_NAME)->shouldBeCalledOnce()->willReturn($data);
		$in->getAttribute($objectAttr)->shouldBeCalledOnce()->willReturn($object);
		$in->withAttribute($errorAttr, $error)->shouldBeCalledOnce()->willReturn($out);

		$dt->transfer($data, Argument::any(), $object, $strategy, $strategy, $validator, $strategy)->shouldBeCalledOnce()->willReturn($error);

		$this->beConstructedWith($dt, $source, $strategy, $validator, $objectAttr, $errorAttr);
		$this->__invoke($in)->shouldBe($out);
	}
}
