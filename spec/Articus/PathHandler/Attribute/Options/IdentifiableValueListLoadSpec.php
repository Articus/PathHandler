<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Options;

use PhpSpec\ObjectBehavior;

class IdentifiableValueListLoadSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$type = 'test_type';
		$emitter = 'test_emitter_attr';
		$emitterArgAttrs = ['test_ieaa_1', 'test_ieaa_2'];
		$receiverFactory = 'test_receiver_factory';
		$receiverFactoryArgAttrs = ['test_vrfaa_1', 'test_vrfaa_2'];
		$listAttr = 'test_list';
		$options = [
			'type' => $type,
			'identifierEmitter' => $emitter,
			'identifierEmitterArgAttrs' => $emitterArgAttrs,
			'valueReceiverFactory' => $receiverFactory,
			'valueReceiverFactoryArgAttrs' => $receiverFactoryArgAttrs,
			'valueListAttr' => $listAttr,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('type', $type);
		$this->shouldHaveProperty('identifierEmitter', $emitter);
		$this->shouldHaveProperty('identifierEmitterArgAttrs', $emitterArgAttrs);
		$this->shouldHaveProperty('valueReceiverFactory', $receiverFactory);
		$this->shouldHaveProperty('valueReceiverFactoryArgAttrs', $receiverFactoryArgAttrs);
		$this->shouldHaveProperty('valueListAttr', $listAttr);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$type = 'test_type';
		$emitter = 'test_emitter_attr';
		$emitterArgAttrs = ['test_ieaa_1', 'test_ieaa_2'];
		$receiverFactory = 'test_receiver_factory';
		$receiverFactoryArgAttrs = ['test_vrfaa_1', 'test_vrfaa_2'];
		$listAttr = 'test_list';
		$options = [
			'type' => $type,
			'identifier_emitter' => $emitter,
			'identifier_emitter_arg_attrs' => $emitterArgAttrs,
			'value_receiver_factory' => $receiverFactory,
			'value_receiver_factory_arg_attrs' => $receiverFactoryArgAttrs,
			'value_list_attr' => $listAttr,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('type', $type);
		$this->shouldHaveProperty('identifierEmitter', $emitter);
		$this->shouldHaveProperty('identifierEmitterArgAttrs', $emitterArgAttrs);
		$this->shouldHaveProperty('valueReceiverFactory', $receiverFactory);
		$this->shouldHaveProperty('valueReceiverFactoryArgAttrs', $receiverFactoryArgAttrs);
		$this->shouldHaveProperty('valueListAttr', $listAttr);
	}
}
