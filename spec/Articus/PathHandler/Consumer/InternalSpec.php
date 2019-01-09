<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Consumer;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;

class InternalSpec extends ObjectBehavior
{
	public function it_returns_preparsed_body(StreamInterface $body)
	{
		$preParsedBody = ['test' => 123];

		$this->shouldImplement(PH\Consumer\ConsumerInterface::class);
		$this->parse($body, $preParsedBody, 'mime/test', [])->shouldBe($preParsedBody);
	}
}
