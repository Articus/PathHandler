<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\Handler;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

#[PHA\Route("/test")]
class ValidAttributes
{
	#[PHA\HttpMethod("NO_ATTRIBUTES")]
	public function noAttributes(Request $request)
	{
	}

	#[PHA\HttpMethod("SEVERAL_ATTRIBUTES")]
	#[PHA\Attribute(name: "test_5")]
	#[PHA\Attribute(name: "test_1", priority: 3)]
	#[PHA\Attribute(name: "test_3", priority: 2)]
	#[PHA\Attribute(name: "test_6")]
	#[PHA\Attribute(name: "test_2", priority: 3, options: ["test_2" => 123])]
	#[PHA\Attribute(name: "test_7", options: ["test_7" => 123])]
	#[PHA\Attribute(name: "test_4", priority: 2, options: ["test_4" => 123])]
	public function severalAttributes(Request $request)
	{
	}
}