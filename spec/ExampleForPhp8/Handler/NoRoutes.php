<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\Handler;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

class NoRoutes
{
	#[PHA\Get()]
	public function read(Request $request)
	{
	}
}