<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\Handler;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

#[PHA\Route("/test")]
class NoMethodsHandlingHttpMethods
{
	public function read(Request $request)
	{
	}

	public function create(Request $request)
	{
	}
}