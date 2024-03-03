<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class MethodNotAllowed extends HttpCode
{
	public function __construct(null|Throwable $previous = null)
	{
		parent::__construct(405, 'Method not allowed', null, $previous);
	}
}
