<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\MethodNotAllowed;
use PhpSpec\ObjectBehavior;

class MethodNotAllowedSpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$this->shouldHaveType(MethodNotAllowed::class);
	}
}
