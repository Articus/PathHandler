<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class NotAcceptable extends HttpCode
{
	public function __construct(null|Throwable $previous = null)
	{
		parent::__construct(406, 'Not acceptable', null, $previous);
	}
}
