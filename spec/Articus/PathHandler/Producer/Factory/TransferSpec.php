<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Producer\Factory;

use Articus\DataTransfer\Mapper\MapperInterface;
use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TransferSpec extends ObjectBehavior
{
	public function it_builds_transfer_producer_without_mapper(ContainerInterface $container, DTService $dt, StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};
		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$this->__invoke($container, 'test', [])->shouldBeAnInstanceOf(PH\Producer\Transfer::class);
	}

	public function it_builds_transfer_producer_with_mapper_service(
		ContainerInterface $container,
		DTService $dt,
		MapperInterface $mapperObject, StreamInterface $stream)
	{
		$mapper = 'test';
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};
		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$container->has($mapper)->shouldBeCalledOnce()->willReturn(true);
		$container->get($mapper)->shouldBeCalledOnce()->willReturn($mapperObject);
		$this->__invoke($container, 'test', ['mapper' => $mapper])->shouldBeAnInstanceOf(PH\Producer\Transfer::class);
	}

	public function it_throws_on_transfer_producer_build_with_invalid_mapper_service(ContainerInterface $container)
	{
		$mapper = 'test';
		$container->has($mapper)->shouldBeCalledOnce()->willReturn(true);
		$container->get($mapper)->shouldBeCalledOnce()->willReturn(null);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'test', ['mapper' => $mapper]]);
	}

	public function it_builds_transfer_producer_with_optionable_mapper_service(
		ServiceLocatorInterface $container,
		DTService $dt,
		MapperInterface $mapperObject, StreamInterface $stream)
	{
		$mapper = ['name' => 'test', 'options' => ['option' => 123]];
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};
		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$container->has($mapper['name'])->shouldBeCalledOnce()->willReturn(true);
		$container->build($mapper['name'], $mapper['options'])->shouldBeCalledOnce()->willReturn($mapperObject);
		$this->__invoke($container, 'test', ['mapper' => $mapper])->shouldBeAnInstanceOf(PH\Producer\Transfer::class);
	}

	public function it_throws_on_transfer_producer_build_with_invalid_optionable_mapper_service(ServiceLocatorInterface $container)
	{
		$mapper = ['name' => 'test', 'options' => ['option' => 123]];
		$container->has($mapper['name'])->shouldBeCalledOnce()->willReturn(true);
		$container->build($mapper['name'], $mapper['options'])->shouldBeCalledOnce()->willReturn(null);
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'test', ['mapper' => $mapper]]);
	}

	public function it_builds_transfer_producer_with_mapper_object(
		ContainerInterface $container,
		DTService $dt,
		MapperInterface $mapperObject, StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};
		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$this->__invoke($container, 'test', ['mapper' => $mapperObject])->shouldBeAnInstanceOf(PH\Producer\Transfer::class);
	}

	public function it_builds_transfer_producer_with_mapper_callable(ContainerInterface $container, DTService $dt, StreamInterface $stream)
	{
		$streamFactory = function () use ($stream)
		{
			return $stream;
		};
		$container->get(StreamInterface::class)->shouldBeCalledOnce()->willReturn($streamFactory);
		$container->get(DTService::class)->shouldBeCalledOnce()->willReturn($dt);
		$this->__invoke($container, 'test', ['mapper' => function(){}])->shouldBeAnInstanceOf(PH\Producer\Transfer::class);
	}

	public function it_throws_on_transfer_producer_build_with_invalid_mapper_config(ServiceLocatorInterface $container)
	{
		$mapper = 123;
		$this->shouldThrow(\LogicException::class)->during('__invoke', [$container, 'test', ['mapper' => $mapper]]);
	}
}
