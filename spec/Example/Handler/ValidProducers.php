<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @PHA\Route(pattern="/test")
 */
class ValidProducers
{
	/**
	 * @PHA\HttpMethod("NO_PRODUCERS")
	 * @param Request $request
	 */
	public function noProducers(Request $request)
	{
	}

	/**
	 * @PHA\HttpMethod("SEVERAL_PRODUCERS")
	 * @PHA\Producer(mediaType="test/5", name="test_5")
	 * @PHA\Producer(mediaType="test/1", name="test_1", priority=3)
	 * @PHA\Producer(mediaType="test/3", name="test_3", priority=2)
	 * @PHA\Producer(mediaType="test/6", name="test_6")
	 * @PHA\Producer(mediaType="test/2", name="test_2", priority=3, options={"test_2": 123})
	 * @PHA\Producer(mediaType="test/7", name="test_7", options={"test_7": 123})
	 * @PHA\Producer(mediaType="test/4", name="test_4", priority=2, options={"test_4": 123})
	 * @param Request $request
	 */
	public function severalProducers(Request $request)
	{
	}
}