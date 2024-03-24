<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Options;

use Articus\PathHandler as PH;
use LogicException;
use PhpSpec\ObjectBehavior;

class AnonymousTransferSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$source = PH\Attribute\Transfer::SOURCE_ROUTE;
		$strategy = ['test_strategy_name', ['aaa' => 111]];
		$validator = ['test_validator_name', ['bbb' => 222]];
		$objectAttr = 'test_object_attr';
		$errorAttr = 'test_error_attr';
		$options = [
			'source' => $source,
			'strategy' => $strategy,
			'validator' => $validator,
			'objectAttr' => $objectAttr,
			'errorAttr' => $errorAttr,
		];
		$this->beConstructedWith($options);
		$this->shouldhaveProperty('source', $source);
		$this->shouldhaveProperty('strategy', $strategy);
		$this->shouldhaveProperty('validator', $validator);
		$this->shouldhaveProperty('objectAttr', $objectAttr);
		$this->shouldhaveProperty('errorAttr', $errorAttr);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$source = PH\Attribute\Transfer::SOURCE_ROUTE;
		$strategy = ['test_strategy_name', ['aaa' => 111]];
		$validator = ['test_validator_name', ['bbb' => 222]];
		$objectAttr = 'test_object_attr';
		$errorAttr = 'test_error_attr';
		$options = [
			'source' => $source,
			'strategy' => $strategy,
			'validator' => $validator,
			'object_attr' => $objectAttr,
			'error_attr' => $errorAttr,
		];
		$this->beConstructedWith($options);
		$this->shouldhaveProperty('source', $source);
		$this->shouldhaveProperty('strategy', $strategy);
		$this->shouldhaveProperty('validator', $validator);
		$this->shouldhaveProperty('objectAttr', $objectAttr);
		$this->shouldhaveProperty('errorAttr', $errorAttr);
	}

	public function it_throws_on_unknown_source()
	{
		$options = [
			'source' => 'unknown_source',
		];
		$exception = new LogicException('Value "unknown_source" for option "source" is not supported.');

		$this->beConstructedWith($options);
		$this->shouldThrow($exception)->duringInstantiation();
	}
}
