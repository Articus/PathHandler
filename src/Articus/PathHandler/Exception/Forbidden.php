<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class Forbidden extends HttpCode
{
	public function __construct(string $reason = 'Forbidden', \Exception $previous = null)
	{
		parent::__construct(403, $reason, null, $previous);
	}
}