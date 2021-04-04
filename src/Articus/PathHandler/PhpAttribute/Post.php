<?php
declare(strict_types=1);

namespace Articus\PathHandler\PhpAttribute;

/**
 * PHP attribute to declare that marked handler class method should be used to handle POST-requests
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Post extends HttpMethod
{
	public function __construct()
	{
		parent::__construct('POST');
	}
}
