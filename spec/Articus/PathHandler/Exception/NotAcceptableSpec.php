<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\NotAcceptable;
use PhpSpec\ObjectBehavior;

class NotAcceptableSpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$this->shouldHaveType(NotAcceptable::class);
	}
}
