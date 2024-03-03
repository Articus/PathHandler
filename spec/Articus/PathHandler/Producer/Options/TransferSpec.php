<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Options;

use PhpSpec\ObjectBehavior;

class TransferSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$subset = 'test_subset';
		$producerName = 'test_name';
		$producerOptions = ['test' => 123];
		$options = [
			'subset' => $subset,
			'producerName' => $producerName,
			'producerOptions' => $producerOptions,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('producerName', $producerName);
		$this->shouldHaveProperty('producerOptions', $producerOptions);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$subset = 'test_subset';
		$producerName = 'test_name';
		$producerOptions = ['test' => 123];
		$options = [
			'subset' => $subset,
			'producer_name' => $producerName,
			'producer_options' => $producerOptions,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('producerName', $producerName);
		$this->shouldHaveProperty('producerOptions', $producerOptions);
	}
}
