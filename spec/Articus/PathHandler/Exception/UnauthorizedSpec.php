<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\Unauthorized;
use PhpSpec\ObjectBehavior;

class UnauthorizedSpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$this->shouldHaveType(Unauthorized::class);
	}
}
