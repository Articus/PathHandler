<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class Forbidden extends HttpCode
{
	public function __construct(mixed $payload = null, null|Throwable $previous = null)
	{
		parent::__construct(403, 'Forbidden', $payload, $previous);
	}
}
