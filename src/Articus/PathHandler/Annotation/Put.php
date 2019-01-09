<?php
declare(strict_types=1);

namespace Articus\PathHandler\Annotation;

/**
 * Annotation to declare that marked handler class method should be used to handle PUT-requests
 * @Annotation
 * @Target({"METHOD"})
 */
class Put extends HttpMethod
{
	public function __construct()
	{
		parent::__construct(['value' => 'PUT']);
	}
}