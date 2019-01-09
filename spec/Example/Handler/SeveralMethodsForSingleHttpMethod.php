<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @PHA\Route(pattern="/test")
 */
class SeveralMethodsForSingleHttpMethod
{
	/**
	 * @PHA\Get()
	 * @param Request $request
	 */
	public function read1(Request $request)
	{
	}

	/**
	 * @PHA\Get()
	 * @param Request $request
	 */
	public function read2(Request $request)
	{
	}
}