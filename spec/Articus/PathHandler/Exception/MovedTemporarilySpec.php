<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\MovedTemporarily;
use PhpSpec\ObjectBehavior;

class MovedTemporarilySpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$location = 'test';
		$this->beConstructedWith($location);
		$this->getHeaders()->shouldIterateAs(['Location' => $location]);
		$this->shouldHaveType(MovedTemporarily::class);
	}
}
