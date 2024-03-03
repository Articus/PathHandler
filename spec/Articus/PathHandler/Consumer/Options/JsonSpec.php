<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Consumer\Options;

use PhpSpec\ObjectBehavior;

class JsonSpec extends ObjectBehavior
{
	public function it_constructs_with_camel_case_option_names()
	{
		$flags = 123;
		$depth = 234;
		$options = [
			'decodeFlags' => $flags,
			'depth' => $depth,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('decodeFlags', $flags);
		$this->shouldHaveProperty('depth', $depth);
	}

	public function it_constructs_with_snake_case_option_names()
	{
		$flags = 123;
		$depth = 234;
		$options = [
			'decode_flags' => $flags,
			'depth' => $depth,
		];
		$this->beConstructedWith($options);
		$this->shouldHaveProperty('decodeFlags', $flags);
		$this->shouldHaveProperty('depth', $depth);
	}
}
