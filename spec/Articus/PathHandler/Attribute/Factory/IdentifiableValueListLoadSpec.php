<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Attribute\Factory;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Subject;
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
		$emitter = static function (string $type, Request $request)
		{
			return $request->getAttribute('ids');
		};

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
		$wrapper->shouldBeAnInstanceOf(PH\Attribute\IdentifiableValueListLoad::class);
		$wrapper->callOnWrappedObject('__invoke', [$in])->shouldBe($out);
	}
}
