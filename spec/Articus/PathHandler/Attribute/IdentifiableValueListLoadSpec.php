<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute;

use LogicException;
use spec\Example\InstanciatorInterface as Invokable;
use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\ServerRequestInterface as Request;

class IdentifiableValueListLoadSpec extends ObjectBehavior
{
	public function it_loads_and_stores_values_if_emitter_and_receiver_do_not_have_arg_attrs(
		IdentifiableValueLoader $loader,
		Invokable $emitter,
		Invokable $receiverFactory,
		$list,
		Request $in,
		Request $out
	)
	{
		$type = 'test_type';
		$ids = [123, 456, 789];
		$values = ['abc', 'def', 'ghi'];
		$emitterArgAttrs = [];
		$receiver = static function () use ($ids, $values, &$list)
		{
			foreach ($ids as $index => $id)
			{
				$tuple = yield;
				if ($tuple !== [$index, $id, $values[$index]])
				{
					throw new LogicException('Invalid tuple');
				}
			}
			return $list;
		};
		$receiverFactoryArgAttrs = [];
		$listAttr = 'test_list_attr';

		$loader->wishMultiple($type, $ids)->shouldBeCalledOnce();
		$loader->get($type, $ids[0])->shouldBeCalledOnce()->willReturn($values[0]);
		$loader->get($type, $ids[1])->shouldBeCalledOnce()->willReturn($values[1]);
		$loader->get($type, $ids[2])->shouldBeCalledOnce()->willReturn($values[2]);
		$emitter->__invoke($type, $in)->shouldBeCalledOnce()->willReturn($ids);
		$receiverFactory->__invoke($type, $in)->shouldBeCalledOnce()->willReturn($receiver());
		$in->withAttribute($listAttr, $list)->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($loader, $type, $emitter, $emitterArgAttrs, $receiverFactory, $receiverFactoryArgAttrs, $listAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_loads_and_stores_values_if_emitter_and_receiver_have_arg_attrs(
		IdentifiableValueLoader $loader,
		Invokable $emitter,
		Invokable $receiverFactory,
		$list,
		Request $in,
		Request $out
	)
	{
		$type = 'test_type';
		$ids = [123, 456, 789];
		$values = ['abc', 'def', 'ghi'];
		$emitterArgAttrs = ['test_e_arg_attr1', 'test_e_arg_attr2'];
		$emitterArgs = ['test_e_arg1', 'test_e_arg2'];
		$receiver = static function () use ($ids, $values, &$list)
		{
			foreach ($ids as $index => $id)
			{
				$tuple = yield;
				if ($tuple !== [$index, $id, $values[$index]])
				{
					throw new LogicException('Invalid tuple');
				}
			}
			return $list;
		};
		$receiverFactoryArgAttrs = ['test_rf_arg_attr1', 'test_rf_arg_attr2'];
		$receiverFactoryArgs = ['test_rf_arg1', 'test_rf_arg2'];
		$listAttr = 'test_list_attr';

		$loader->wishMultiple($type, $ids)->shouldBeCalledOnce();
		$loader->get($type, $ids[0])->shouldBeCalledOnce()->willReturn($values[0]);
		$loader->get($type, $ids[1])->shouldBeCalledOnce()->willReturn($values[1]);
		$loader->get($type, $ids[2])->shouldBeCalledOnce()->willReturn($values[2]);
		$emitter->__invoke($type, $emitterArgs[0], $emitterArgs[1])->shouldBeCalledOnce()->willReturn($ids);
		$receiverFactory->__invoke($type, $receiverFactoryArgs[0], $receiverFactoryArgs[1])->shouldBeCalledOnce()->willReturn($receiver());
		$in->getAttribute($emitterArgAttrs[0])->shouldBeCalledOnce()->willReturn($emitterArgs[0]);
		$in->getAttribute($emitterArgAttrs[1])->shouldBeCalledOnce()->willReturn($emitterArgs[1]);
		$in->getAttribute($receiverFactoryArgAttrs[0])->shouldBeCalledOnce()->willReturn($receiverFactoryArgs[0]);
		$in->getAttribute($receiverFactoryArgAttrs[1])->shouldBeCalledOnce()->willReturn($receiverFactoryArgs[1]);
		$in->withAttribute($listAttr, $list)->shouldBeCalledOnce()->willReturn($out);

		$this->beConstructedWith($loader, $type, $emitter, $emitterArgAttrs, $receiverFactory, $receiverFactoryArgAttrs, $listAttr);
		$this->__invoke($in)->shouldBe($out);
	}

	public function it_throws_if_there_is_no_value_for_one_of_ids(
		IdentifiableValueLoader $loader,
		Invokable $emitter,
		Invokable $receiverFactory,
		$list,
		Request $in
	)
	{
		$type = 'test_type';
		$ids = [12, 34, 56, 78];
		$values = ['abc', null, 'def', null];
		$emitterArgAttrs = [];
		$receiver = static function () use ($ids, $values, &$list)
		{
			foreach ($ids as $index => $id)
			{
				if ($values[$index] !== null)
				{
					$tuple = yield;
					if ($tuple !== [$index, $id, $values[$index]])
					{
						throw new LogicException('Invalid tuple');
					}
				}
			}
			return $list;
		};
		$receiverFactoryArgAttrs = [];
		$listAttr = 'test_list_attr';
		$error = new PH\Exception\UnprocessableEntity([
			'unknownIdentifiers' => 'Unknown identifier(s): 34, 78'
		]);

		$loader->wishMultiple($type, $ids)->shouldBeCalledOnce();
		$loader->get($type, $ids[0])->shouldBeCalledOnce()->willReturn($values[0]);
		$loader->get($type, $ids[1])->shouldBeCalledOnce()->willReturn($values[1]);
		$loader->get($type, $ids[2])->shouldBeCalledOnce()->willReturn($values[2]);
		$loader->get($type, $ids[3])->shouldBeCalledOnce()->willReturn($values[3]);
		$emitter->__invoke($type, $in)->shouldBeCalledOnce()->willReturn($ids);
		$receiverFactory->__invoke($type, $in)->shouldBeCalledOnce()->willReturn($receiver());

		$this->beConstructedWith($loader, $type, $emitter, $emitterArgAttrs, $receiverFactory, $receiverFactoryArgAttrs, $listAttr);
		$this->shouldThrow($error)->during('__invoke', [$in]);
	}
}
