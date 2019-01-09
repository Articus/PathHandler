<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\Forbidden;
use PhpSpec\ObjectBehavior;

class ForbiddenSpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$this->shouldHaveType(Forbidden::class);
	}
}
