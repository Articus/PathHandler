<?php
declare(strict_types=1);

namespace spec\Example\Handler;

use Articus\PathHandler\Annotation as PHA;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @PHA\Route(pattern="/test")
 */
class SeveralRequiredParametersMethod
{
	/**
	 * @PHA\HttpMethod("TEST")
	 * @param Request $request
	 */
	public function testMethod(Request $request, $parameter)
	{
	}
}