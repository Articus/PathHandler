<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Options;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;

class IdentifiableValueListLoadSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$options = [
			'type' => 'test_type',
			'identifierEmitter' => 'test_emitter_attr',
			'identifierEmitterArgAttrs' => ['test_ieaa_1', 'test_ieaa_2'],
			'valueReceiverFactory' => 'test_receiver_factory',
			'valueReceiverFactoryArgAttrs' => ['test_vrfaa_1', 'test_vrfaa_2'],
			'valueListAttr' => 'test_list',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\IdentifiableValueListLoad::class);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$options = [
			'type' => 'test_type',
			'identifier_emitter' => 'test_emitter_attr',
			'identifier_emitter_arg_attrs' => ['test_ieaa_1', 'test_ieaa_2'],
			'value_receiver_factory' => 'test_receiver_factory',
			'value_receiver_factory_arg_attrs' => ['test_vrfaa_1', 'test_vrfaa_2'],
			'value_list_attr' => 'test_list',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\IdentifiableValueListLoad::class);
	}

	public function it_constructs_with_camel_case_option_aliases()
	{
		$options = [
			'type' => 'test_type',
			'idEmitter' => 'test_emitter_attr',
			'idEmitterArgAttrs' => ['test_ieaa_1', 'test_ieaa_2'],
			'valueReceiverFactory' => 'test_receiver_factory',
			'valueReceiverFactoryArgAttrs' => ['test_vrfaa_1', 'test_vrfaa_2'],
			'listAttr' => 'test_list',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\IdentifiableValueListLoad::class);
	}

	public function it_constructs_with_snake_case_option_aliases()
	{
		$options = [
			'type' => 'test_type',
			'id_emitter' => 'test_emitter_attr',
			'id_emitter_arg_attrs' => ['test_ieaa_1', 'test_ieaa_2'],
			'value_receiver_factory' => 'test_receiver_factory',
			'value_receiver_factory_arg_attrs' => ['test_vrfaa_1', 'test_vrfaa_2'],
			'list_attr' => 'test_list',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\IdentifiableValueListLoad::class);
	}

	public function it_throws_it_there_is_no_type_option()
	{
		$options = [];
		$exception = new \LogicException('Option "type" is not set');
		$this->beConstructedWith($options);
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_it_there_is_no_id_emitter_option()
	{
		$options = [
			'type' => 'test_type',
		];
		$exception = new \LogicException('Option "identifierEmitter" is not set');
		$this->beConstructedWith($options);
		$this->shouldThrow($exception)->duringInstantiation();
	}
}
