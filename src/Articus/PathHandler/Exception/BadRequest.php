<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class BadRequest extends HttpCode
{
	public function __construct(mixed $payload = null, null|Throwable $previous = null)
	{
		parent::__construct(400, 'Bad request', $payload, $previous);
	}
}
