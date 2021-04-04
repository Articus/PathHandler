<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\Handler;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

#[PHA\Route(pattern: "/5")]
#[PHA\Route(pattern: "/1", priority: 3)]
#[PHA\Route(pattern: "/3", priority: 2, name: "test_3")]
#[PHA\Route(pattern: "/6", name: "test_6")]
#[PHA\Route(pattern: "/2", priority: 3, defaults: ["test_2" => 123])]
#[PHA\Route(pattern: "/7", defaults: ["test_7" => 123])]
#[PHA\Route(pattern: "/4", priority: 2, name: "test_4", defaults: ["test_4" => 123])]
#[PHA\Route(pattern: "/8", name: "test_8", defaults: ["test_8" => 123])]
class ValidRoutes
{
	#[PHA\Get()]
	public function read(Request $request)
	{
	}
}