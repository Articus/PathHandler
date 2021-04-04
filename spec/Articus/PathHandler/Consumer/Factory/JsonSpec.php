<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Consumer\Factory;

use Articus\PathHandler as PH;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;

class JsonSpec extends ObjectBehavior
{
	public function it_builds_json_consumer_with_default_options(ContainerInterface $container)
	{
		$service = $this->__invoke($container, 'test');
		$service->shouldBeAnInstanceOf(PH\Consumer\Json::class);
		$service->shouldHaveProperty('parseAsStdClass', false);
	}

	public function it_builds_json_consumer_with_specified_options(ContainerInterface $container)
	{
		$service = $this->__invoke($container, 'test', ['parse_as_std_class' => true]);
		$service->shouldBeAnInstanceOf(PH\Consumer\Json::class);
		$service->shouldHaveProperty('parseAsStdClass', true);
	}
}
