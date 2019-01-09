<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\BadRequest;
use PhpSpec\ObjectBehavior;

class BadRequestSpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$this->shouldHaveType(BadRequest::class);
	}
}
