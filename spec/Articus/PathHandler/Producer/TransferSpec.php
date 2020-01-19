<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;

class TransferSpec extends ObjectBehavior
{
	protected const SUBSET = 'testSubset';

	public function let(DTService $dt, StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream->getWrappedObject();
		};

		$this->beConstructedWith($streamFactory, $dt, self::SUBSET);
		$this->shouldImplement(PH\Producer\ProducerInterface::class);
	}

	public function it_transfers_scalar_to_json(DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = 123;
		$json = '123';

		$dt->extractFromTypedData()->shouldNotBeCalled();

		$stream->write($json)->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();

		$this->assemble($objectOrArray)->shouldBe($stream);
	}

	public function it_transfers_object_to_json(DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = new \stdClass();
		$json = '[]';

		$dt->extractFromTypedData($objectOrArray, self::SUBSET)->shouldBeCalledOnce()->willReturn([]);

		$stream->write($json)->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();

		$this->assemble($objectOrArray)->shouldBe($stream);
	}

	public function it_transfers_array_of_scalars_to_json(DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = [123, 'qwer'];
		$json = '[123,"qwer"]';

		$dt->extractFromTypedData()->shouldNotBeCalled();

		$stream->write($json)->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();

		$this->assemble($objectOrArray)->shouldBe($stream);
	}

	public function it_transfers_array_of_objects_to_json(DTService $dt, StreamInterface $stream)
	{
		$objectOrArray = [new \stdClass()];
		$json = '[[]]';

		$dt->extractFromTypedData($objectOrArray[0], self::SUBSET)->shouldBeCalledOnce()->willReturn([]);

		$stream->write($json)->shouldBeCalledOnce();
		$stream->rewind()->shouldBeCalledOnce();

		$this->assemble($objectOrArray)->shouldBe($stream);
	}
}
