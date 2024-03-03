<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

#[PHA\Route("/test")]
class ValidHttpMethods
{
	#[PHA\Get()]
	#[PHA\HttpMethod("HEAD")]
	public function read(Request $request)
	{
	}

	#[PHA\Post()]
	public function create(Request $request)
	{
	}

	#[PHA\Patch()]
	#[PHA\Put()]
	public function update(Request $request)
	{
	}

	#[PHA\Delete()]
	public function delete(Request $request)
	{
	}

	#[PHA\HttpMethod("OPTIONS")]
	#[PHA\HttpMethod("CUSTOM_METHOD")]
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