<?php
declare(strict_types=1);

namespace Articus\PathHandler\Annotation;

/**
 * Annotation to declare that marked handler class method should be used to handle GET-requests
 * @Annotation
 * @Target({"METHOD"})
 */
class Get extends HttpMethod
{
	public function __construct()
	{
		parent::__construct(['value' => 'GET']);
	}
}