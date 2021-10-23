<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class Unauthorized extends HttpCode
{
	public function __construct(?string $payload = null, \Exception $previous = null)
	{
		parent::__construct(401, 'Unauthorized', $payload, $previous);
	}
}