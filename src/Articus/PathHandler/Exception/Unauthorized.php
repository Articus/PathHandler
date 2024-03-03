<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class Unauthorized extends HttpCode
{
	public function __construct(mixed $payload = null, null|Throwable $previous = null)
	{
		parent::__construct(401, 'Unauthorized', $payload, $previous);
	}
}
