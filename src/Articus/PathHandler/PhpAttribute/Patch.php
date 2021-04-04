<?php
declare(strict_types=1);

namespace Articus\PathHandler\PhpAttribute;

/**
 * PHP attribute to declare that marked handler class method should be used to handle PATCH-requests
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Patch extends HttpMethod
{
	public function __construct()
	{
		parent::__construct('PATCH');
	}
}
