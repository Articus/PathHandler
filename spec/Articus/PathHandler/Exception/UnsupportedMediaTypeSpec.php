<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\UnsupportedMediaType;
use PhpSpec\ObjectBehavior;

class UnsupportedMediaTypeSpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$this->shouldHaveType(UnsupportedMediaType::class);
	}
}
