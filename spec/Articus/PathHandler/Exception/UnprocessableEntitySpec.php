<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Exception;

use Articus\PathHandler\Exception\UnprocessableEntity;
use PhpSpec\ObjectBehavior;

class UnprocessableEntitySpec extends ObjectBehavior
{
	function it_is_initializable()
	{
		$validationResult = ['test' => 123];
		$this->beConstructedWith($validationResult);
		$this->shouldHaveType(UnprocessableEntity::class);
		$this->getPayload()->shouldBe($validationResult);
	}
}
