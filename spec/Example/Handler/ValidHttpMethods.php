<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @PHA\Route(pattern="/test")
 */
class ValidHttpMethods
{
	/**
	 * @PHA\Get()
	 * @PHA\HttpMethod("HEAD")
	 * @param Request $request
	 */
	public function read(Request $request)
	{
	}

	/**
	 * @PHA\Post()
	 * @param Request $request
	 */
	public function create(Request $request)
	{
	}

	/**
	 * @PHA\Patch()
	 * @PHA\Put()
	 * @param Request $request
	 */
	public function update(Request $request)
	{
	}

	/**
	 * @PHA\Delete()
	 * @param Request $request
	 */
	public function delete(Request $request)
	{
	}

	/**
	 * @PHA\HttpMethod("OPTIONS")
	 * @PHA\HttpMethod("CUSTOM_METHOD")
	 * @param Request $request
	 */
	public function monitor(Request $request)
	{
	}

	public function publicMethod()
	{
	}

	protected function protectedMethod()
	{
	}

	private function privateMethod()
	{
	}
}