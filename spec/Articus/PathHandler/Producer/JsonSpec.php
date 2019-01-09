<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;

class JsonSpec extends ObjectBehavior
{
	public function let()
	{
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
	}

	public function it_produces_null_from_null_data()
	{
		$this->assemble(null)->shouldBe(null);
	}

	public function it_produces_json_from_json_serializable_data()
	{
		$data = ['test' => 123];
		$stream = $this->assemble($data);
		$stream->getContents()->shouldBe(\json_encode($data));
	}

	public function it_throws_on_non_json_serializable_data()
	{
		$data = "\xB1\x31";
		$this->shouldThrow(\InvalidArgumentException::class)->during('assemble', [$data]);
	}
}
