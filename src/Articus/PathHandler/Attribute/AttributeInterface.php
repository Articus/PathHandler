<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Interface for attributes - services that are used to somehow modify initial request, usually by "withAttribute" method
 */
interface AttributeInterface
{
	/**
	 * Modifies initial request
	 * @param Request $request initial request
	 * @return Request modified request
	 */
	public function __invoke(Request $request): Request;
}
