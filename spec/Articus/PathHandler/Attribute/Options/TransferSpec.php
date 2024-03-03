<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Options;

use Articus\PathHandler as PH;
use LogicException;
use PhpSpec\ObjectBehavior;
use stdClass;

class TransferSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$source = PH\Attribute\Transfer::SOURCE_ROUTE;
		$type = stdClass::class;
		$subset = 'test_subset';
		$objectAttr = 'test_object_attr';
		$instanciator = 'test_instanciator';
		$instanciatorArgAttrs = ['test_attr_1', 'test_attr_2'];
		$errorAttr = 'test_error_attr';
		$options = [
			'source' => $source,
			'type' => $type,
			'subset' => $subset,
			'objectAttr' => $objectAttr,
			'instanciator' => $instanciator,
			'instanciatorArgAttrs' => $instanciatorArgAttrs,
			'errorAttr' => $errorAttr,
		];
		$this->beConstructedWith($options);
		$this->shouldhaveProperty('source', $source);
		$this->shouldhaveProperty('type', $type);
		$this->shouldhaveProperty('subset', $subset);
		$this->shouldhaveProperty('objectAttr', $objectAttr);
		$this->shouldhaveProperty('instanciator', $instanciator);
		$this->shouldhaveProperty('instanciatorArgAttrs', $instanciatorArgAttrs);
		$this->shouldhaveProperty('errorAttr', $errorAttr);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$source = PH\Attribute\Transfer::SOURCE_ROUTE;
		$type = stdClass::class;
		$subset = 'test_subset';
		$objectAttr = 'test_object_attr';
		$instanciator = 'test_instanciator';
		$instanciatorArgAttrs = ['test_attr_1', 'test_attr_2'];
		$errorAttr = 'test_error_attr';
		$options = [
			'source' => $source,
			'type' => $type,
			'subset' => $subset,
			'object_attr' => $objectAttr,
			'instanciator' => $instanciator,
			'instanciator_arg_attrs' => $instanciatorArgAttrs,
			'error_attr' => $errorAttr,
		];
		$this->beConstructedWith($options);
		$this->shouldhaveProperty('source', $source);
		$this->shouldhaveProperty('type', $type);
		$this->shouldhaveProperty('subset', $subset);
		$this->shouldhaveProperty('objectAttr', $objectAttr);
		$this->shouldhaveProperty('instanciator', $instanciator);
		$this->shouldhaveProperty('instanciatorArgAttrs', $instanciatorArgAttrs);
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

	public function it_throws_on_non_class_type()
	{
		$options = [
			'type' => 'non_class',
		];
		$exception = new LogicException('Option "type" should be a valid class name, not "non_class".');

		$this->beConstructedWith($options);
		$this->shouldThrow($exception)->duringInstantiation();
	}
}
