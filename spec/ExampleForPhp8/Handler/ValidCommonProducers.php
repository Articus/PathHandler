<?php
declare(strict_types=1);

namespace spec\ExampleForPhp8\Handler;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

#[PHA\Route("/test")]
#[PHA\Producer(mediaType: "test/c5", name: "test_c5")]
#[PHA\Producer(mediaType: "test/c1", name: "test_c1", priority: 3)]
#[PHA\Producer(mediaType: "test/c3", name: "test_c3", priority: 2)]
#[PHA\Producer(mediaType: "test/c6", name: "test_c6")]
#[PHA\Producer(mediaType: "test/c2", name: "test_c2", priority: 3, options: ["test_c2" => 123])]
#[PHA\Producer(mediaType: "test/c7", name: "test_c7", options: ["test_c7" => 123])]
#[PHA\Producer(mediaType: "test/c4", name: "test_c4", priority: 2, options: ["test_c4" => 123])]
class ValidCommonProducers
{
	#[PHA\HttpMethod("NO_PRODUCERS")]
	public function noProducers(Request $request)
	{
	}

	#[PHA\HttpMethod("SEVERAL_PRODUCERS")]
	#[PHA\Producer(mediaType: "test/5", name: "test_5")]
	#[PHA\Producer(mediaType: "test/1", name: "test_1", priority: 3)]
	#[PHA\Producer(mediaType: "test/3", name: "test_3", priority: 2)]
	#[PHA\Producer(mediaType: "test/6", name: "test_6")]
	#[PHA\Producer(mediaType: "test/2", name: "test_2", priority: 3, options: ["test_2" => 123])]
	#[PHA\Producer(mediaType: "test/7", name: "test_7", options: ["test_7" => 123])]
	#[PHA\Producer(mediaType: "test/4", name: "test_4", priority: 2, options: ["test_4" => 123])]
	public function severalProducers(Request $request)
	{
	}
}