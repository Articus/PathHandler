<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler as PH;
use LogicException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ServerRequestInterface as Request;

class IdentifiableValueLoadSpec extends ObjectBehavior
{
	public function it_throws_if_there_is_no_id(IdentifiableValueLoader $loader, Request $request)
	{
		$type = 'test';
		$idAttr = 'id_attr';
		$valueAttr = 'value_attr';

		$request->getAttribute($idAttr)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($loader, $type, $idAttr, $valueAttr);
		$this->shouldThrow(LogicException::class)->during('__invoke', [$request]);
	}

	public function it_throws_on_invalid_id(IdentifiableValueLoader $loader, Request $request, $id)
	{
		$type = 'test';
		$idAttr = 'id_attr';
		$valueAttr = 'value_attr';

		$request->getAttribute($idAttr)->shouldBeCalledOnce()->willReturn($id);

		$this->beConstructedWith($loader, $type, $idAttr, $valueAttr);
		$this->shouldThrow(LogicException::class)->during('__invoke', [$request]);
	}

	public function it_throws_if_there_is_no_value(IdentifiableValueLoader $loader, Request $request)
	{
		$type = 'test';
		$idAttr = 'id_attr';
		$valueAttr = 'value_attr';
		$id = 123;

		$request->getAttribute($idAttr)->shouldBeCalledOnce()->willReturn($id);
		$loader->get($type, $id)->shouldBeCalledOnce()->willReturn(null);

		$this->beConstructedWith($loader, $type, $idAttr, $valueAttr);
		$this->shouldThrow(PH\Exception\NotFound::class)->during('__invoke', [$request]);
	}

	public function it_loads_and_stores_value(IdentifiableValueLoader $loader, Request $in, Request $out, $value)
	{
		$type = 'test';
		$idAttr = 'id_attr';
		$valueAttr = 'value_attr';
		$id = 123;

		$in->getAttribute($idAttr)->shouldBeCalledOnce()->willReturn($id);
		$loader->get($type, $id)->shouldBeCalledOnce()->willReturn($value);
		$in->withAttribute($valueAttr, $value)->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($loader, $type, $idAttr, $valueAttr);
		$this->__invoke($in)->shouldBe($out);
	}
}
