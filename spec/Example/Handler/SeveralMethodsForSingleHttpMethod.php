<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\PhpAttribute as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

#[PHA\Route("/test")]
class SeveralMethodsForSingleHttpMethod
{
	#[PHA\Get()]
	public function read1(Request $request)
	{
	}

	#[PHA\Get()]
	public function read2(Request $request)
	{
	}
}