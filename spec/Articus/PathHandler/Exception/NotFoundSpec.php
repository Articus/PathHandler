<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\NotFound;
use PhpSpec\ObjectBehavior;

class NotFoundSpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$this->shouldHaveType(NotFound::class);
	}
}
