<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class BadRequest extends HttpCode
{
	public function __construct(string $reason = 'Bad request', \Exception $previous = null)
	{
		parent::__construct(400, $reason, null, $previous);
	}
}