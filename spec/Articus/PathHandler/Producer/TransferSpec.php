<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;
use stdClass;

class TransferSpec extends ObjectBehavior
{
	protected const SUBSET = 'testSubset';

	public function it_passes_null_to_producer(PH\Producer\ProducerInterface $producer, DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = null;

		$dt->extractFromTypedData()->shouldNotBeCalled();
		$producer->assemble($objectOrArray)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($producer, $dt, self::SUBSET);
		$this->assemble($objectOrArray)->shouldBe($stream);
	}

	public function it_passes_scalar_to_producer(PH\Producer\ProducerInterface $producer, DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = 123;

		$dt->extractFromTypedData()->shouldNotBeCalled();
		$producer->assemble($objectOrArray)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($producer, $dt, self::SUBSET);
		$this->assemble($objectOrArray)->shouldBe($stream);
	}

	public function it_transfers_object_and_passes_data_to_producer(PH\Producer\ProducerInterface $producer, DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = new stdClass();
		$data = ['test' => 123];

		$dt->extractFromTypedData($objectOrArray, self::SUBSET)->shouldBeCalledOnce()->willReturn($data);
		$producer->assemble($data)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($producer, $dt, self::SUBSET);
		$this->assemble($objectOrArray)->shouldBe($stream);
	}

	public function it_passes_array_of_scalars_to_producer(PH\Producer\ProducerInterface $producer, DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = [123, 'qwer'];

		$dt->extractFromTypedData()->shouldNotBeCalled();
		$producer->assemble($objectOrArray)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($producer, $dt, self::SUBSET);
		$this->assemble($objectOrArray)->shouldBe($stream);
	}

	public function it_transfers_array_of_objects_and_passes_data_to_producer(PH\Producer\ProducerInterface $producer, DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = [new stdClass()];
		$data = [['test' => 123]];

		$dt->extractFromTypedData($objectOrArray[0], self::SUBSET)->shouldBeCalledOnce()->willReturn($data[0]);
		$producer->assemble($data)->shouldBeCalledOnce()->willReturn($stream);

		$this->beConstructedWith($producer, $dt, self::SUBSET);
		$this->assemble($objectOrArray)->shouldBe($stream);
	}
}
