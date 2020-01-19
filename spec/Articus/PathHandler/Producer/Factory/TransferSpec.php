<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;

class TransferSpec extends ObjectBehavior
{
	public function it_builds_transfer_producer_without_subset(ContainerInterface $container, DTService $dt, StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};
		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$this->__invoke($container, 'test', [])->shouldBeAnInstanceOf(PH\Producer\Transfer::class);
	}

	public function it_builds_transfer_producer_with_subset(ContainerInterface $container, DTService $dt, StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};
		$subset = 'testSubset';
		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$this->__invoke($container, 'test', ['subset' => $subset])->shouldBeAnInstanceOf(PH\Producer\Transfer::class);
	}
}
