<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

#[PHA\Route("/test")]
class ValidConsumers
{
	#[PHA\HttpMethod("NO_CONSUMERS")]
	public function noConsumers(Request $request)
	{
	}

	#[PHA\HttpMethod("SEVERAL_CONSUMERS")]
	#[PHA\Consumer(mediaRange: "*/*", name: "test_5")]
	#[PHA\Consumer(mediaRange: "*/*", name: "test_1", priority: 3)]
	#[PHA\Consumer(mediaRange: "test/3", name: "test_3", priority: 2)]
	#[PHA\Consumer(mediaRange: "test/6", name: "test_6")]
	#[PHA\Consumer(mediaRange: "*/*", name: "test_2", options: ["test_2" => 123], priority: 3)]
	#[PHA\Consumer(mediaRange: "*/*", name: "test_7", options: ["test_7" => 123])]
	#[PHA\Consumer(mediaRange: "test/4", name: "test_4", options: ["test_4" => 123], priority: 2)]
	#[PHA\Consumer(mediaRange: "test/8", name: "test_8", options: ["test_8" => 123])]
	public function severalConsumers(Request $request)
	{
	}
}