<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @PHA\Route(pattern="/test")
 * @PHA\Consumer(name="test_c5")
 * @PHA\Consumer(name="test_c1", priority=3)
 * @PHA\Consumer(name="test_c3", priority=2, mediaRange="test/c3")
 * @PHA\Consumer(name="test_c6", mediaRange="test/c6")
 * @PHA\Consumer(name="test_c2", priority=3, options={"test_c2": 123})
 * @PHA\Consumer(name="test_c7", options={"test_c7": 123})
 * @PHA\Consumer(name="test_c4", priority=2, mediaRange="test/c4", options={"test_c4": 123})
 * @PHA\Consumer(name="test_c8", mediaRange="test/c8", options={"test_c8": 123})
 */
class ValidCommonConsumers
{
	/**
	 * @PHA\HttpMethod("NO_CONSUMERS")
	 * @param Request $request
	 */
	public function noConsumers(Request $request)
	{
	}

	/**
	 * @PHA\HttpMethod("SEVERAL_CONSUMERS")
	 * @PHA\Consumer(name="test_5")
	 * @PHA\Consumer(name="test_1", priority=3)
	 * @PHA\Consumer(name="test_3", priority=2, mediaRange="test/3")
	 * @PHA\Consumer(name="test_6", mediaRange="test/6")
	 * @PHA\Consumer(name="test_2", priority=3, options={"test_2": 123})
	 * @PHA\Consumer(name="test_7", options={"test_7": 123})
	 * @PHA\Consumer(name="test_4", priority=2, mediaRange="test/4", options={"test_4": 123})
	 * @PHA\Consumer(name="test_8", mediaRange="test/8", options={"test_8": 123})
	 * @param Request $request
	 */
	public function severalConsumers(Request $request)
	{
	}
}