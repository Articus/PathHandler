<?php
declare(strict_types=1);

namespace Articus\PathHandler\PhpAttribute;

/**
 * PHP attribute to declare that marked handler class method should be used to handle PUT-requests
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Put extends HttpMethod
{
	public function __construct()
	{
		parent::__construct('PUT');
	}
}
