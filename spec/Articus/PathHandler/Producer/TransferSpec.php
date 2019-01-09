<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Articus\DataTransfer\Mapper\MapperInterface;
use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TransferSpec extends ObjectBehavior
{
	public function let(DTService $dt, MapperInterface $mapper)
	{
		$this->beConstructedWith($dt, $mapper);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
	}

	public function it_transfers_scalar_to_json(DTService $dt)
	{
		$objectOrArray = 123;
		$json = '123';

		$dt->transfer()->shouldNotBeCalled();

		$stream = $this->assemble($objectOrArray);
		$stream->getContents()->shouldBe($json);
	}

	public function it_transfers_object_to_json(DTService $dt, MapperInterface $mapper)
	{
		//TODO find a way to pass argument by reference
		$objectOrArray = new \stdClass();
		$data = [];
		$json = '[]';

		$dt->transfer($objectOrArray, $data, $mapper)->shouldBeCalledOnce()->willReturn([]);

		$stream = $this->assemble($objectOrArray);
		$stream->getContents()->shouldBe($json);
	}

	public function it_transfers_array_of_scalars_to_json(DTService $dt)
	{
		//TODO find a way to pass argument by reference
		$objectOrArray = [123, 'qwer'];
		$json = '[123,"qwer"]';

		$dt->transfer()->shouldNotBeCalled();

		$stream = $this->assemble($objectOrArray);
		$stream->getContents()->shouldBe($json);
	}

	public function it_transfers_array_of_objects_to_json(DTService $dt, MapperInterface $mapper)
	{
		//TODO find a way to pass argument by reference
		$objectOrArray = [new \stdClass()];
		$data = [];
		$json = '[[]]';

		$dt->transfer($objectOrArray[0], $data, $mapper)->shouldBeCalledOnce()->willReturn([]);

		$stream = $this->assemble($objectOrArray);
		$stream->getContents()->shouldBe($json);
	}

	public function it_throws_on_non_transferable_data(DTService $dt, MapperInterface $mapper)
	{
		//TODO find a way to pass argument by reference
		$objectOrArray = new \stdClass();
		$data = [];

		$dt->transfer($objectOrArray, $data, $mapper)->shouldBeCalledOnce()->willReturn(['wrong' => 123]);

		$this->shouldThrow(\InvalidArgumentException::class)->during('assemble', [$objectOrArray]);
	}
}