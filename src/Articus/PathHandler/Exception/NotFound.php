<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class NotFound extends HttpCode
{
	public function __construct(null|Throwable $previous = null)
	{
		parent::__construct(404, 'Not found', null, $previous);
	}
}
