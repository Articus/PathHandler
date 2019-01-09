<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class Unauthorized extends HttpCode
{
	public function __construct(string $reason = 'Unauthorized', \Exception $previous = null)
	{
		parent::__construct(401, $reason, null, $previous);
	}
}