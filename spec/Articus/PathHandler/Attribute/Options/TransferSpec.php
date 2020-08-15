<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Options;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;

class TransferSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$options = [
			'source' => 'test_source',
			'type' => 'test_type',
			'subset' => 'test_subset',
			'objectAttr' => 'test_object_attr',
			'instanciator' => 'test_instanciator',
			'instanciatorArgAttrs' => ['test_attr_1', 'test_attr_2'],
			'errorAttr' => 'test_error_attr',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\Transfer::class);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$options = [
			'source' => 'test_source',
			'type' => 'test_type',
			'subset' => 'test_subset',
			'object_attr' => 'test_object_attr',
			'instanciator' => 'test_instanciator',
			'instanciator_arg_attrs' => ['test_attr_1', 'test_attr_2'],
			'error_attr' => 'test_error_attr',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\Transfer::class);
	}

	public function it_throws_it_there_is_no_type_option()
	{
		$options = [];
		$exception = new \LogicException('Option "type" is not set');
		$this->beConstructedWith($options);
		$this->shouldThrow($exception)->duringInstantiation();
	}
}
