<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Options;

use PhpSpec\ObjectBehavior;

class IdentifiableValueLoadSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$type = 'test_type';
		$idAttr = 'test_id_attr';
		$valueAttr = 'test_value_attr';
		$options = [
			'type' => $type,
			'identifierAttr' => $idAttr,
			'valueAttr' => $valueAttr,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('type', $type);
		$this->shouldHaveProperty('identifierAttr', $idAttr);
		$this->shouldHaveProperty('valueAttr', $valueAttr);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$type = 'test_type';
		$idAttr = 'test_id_attr';
		$valueAttr = 'test_value_attr';
		$options = [
			'type' => $type,
			'identifier_attr' => $idAttr,
			'value_attr' => $valueAttr,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('type', $type);
		$this->shouldHaveProperty('identifierAttr', $idAttr);
		$this->shouldHaveProperty('valueAttr', $valueAttr);
	}
}
