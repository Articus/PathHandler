<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @PHA\Route(pattern="/test")
 */
class EmptyHttpMethod
{
	/**
	 * @PHA\HttpMethod()
	 * @param Request $request
	 */
	public function read(Request $request)
	{
	}
}