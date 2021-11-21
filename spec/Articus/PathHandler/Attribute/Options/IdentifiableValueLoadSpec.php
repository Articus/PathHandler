<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Options;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;

class IdentifiableValueLoadSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$options = [
			'type' => 'test_type',
			'identifierAttr' => 'test_id_attr',
			'valueAttr' => 'test_value_attr',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\IdentifiableValueLoad::class);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$options = [
			'type' => 'test_type',
			'identifier_attr' => 'test_id_attr',
			'value_attr' => 'test_value_attr',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\IdentifiableValueLoad::class);
	}

	public function it_constructs_with_camel_case_option_aliases()
	{
		$options = [
			'type' => 'test_type',
			'idAttr' => 'test_id_attr',
			'valueAttr' => 'test_value_attr',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\IdentifiableValueLoad::class);
	}

	public function it_constructs_with_snake_case_option_aliases()
	{
		$options = [
			'type' => 'test_type',
			'id_attr' => 'test_id_attr',
			'value_attr' => 'test_value_attr',
		];
		$this->beConstructedWith($options);
		$this->shouldBeAnInstanceOf(PH\Attribute\Options\IdentifiableValueLoad::class);
	}

	public function it_throws_it_there_is_no_type_option()
	{
		$options = [];
		$exception = new \LogicException('Option "type" is not set');
		$this->beConstructedWith($options);
		$this->shouldThrow($exception)->duringInstantiation();
	}
}
