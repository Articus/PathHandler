<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class IdentifiableValueListLoadSpec extends ObjectBehavior
{
	public function it_builds_attribute_with_simple_config(
		ContainerInterface $container,
		IdentifiableValueLoader $loader,
		Request $in,
		Request $out
	)
	{
		$type = 'test_type';
		$ids = [123, 456, 789];
		$values = ['abc', 'def', 'ghi'];
		$options = [
			'type' => $type,
			'identifierEmitter' => 'test_emitter',
		];
		$emitter = static fn (string $type, Request $request) => $request->getAttribute('ids');

		$container->get(IdentifiableValueLoader::class)->shouldBeCalledOnce()->willReturn($loader);
		$container->get('test_emitter')->shouldBeCalledOnce()->willReturn($emitter);
		$loader->wishMultiple($type, $ids)->shouldBeCalledOnce();
		$loader->get($type, $ids[0])->shouldBeCalledOnce()->willReturn($values[0]);
		$loader->get($type, $ids[1])->shouldBeCalledOnce()->willReturn($values[1]);
		$loader->get($type, $ids[2])->shouldBeCalledOnce()->willReturn($values[2]);
		$in->getAttribute('ids')->shouldBeCalledOnce()->willReturn($ids);
		$in->withAttribute('list', $values)->shouldBeCalledOnce()->willReturn($out);

		/** @var Subject $wrapper */
		$wrapper = $this->__invoke($container, 'test', $options);
		$wrapper->shouldHaveProperty('loader', $loader);
		$wrapper->shouldHaveProperty('type', $type);
		$wrapper->shouldHaveProperty('identifierEmitter', $emitter);
		$wrapper->callOnWrappedObject('__invoke', [$in])->shouldBe($out);
	}
}
