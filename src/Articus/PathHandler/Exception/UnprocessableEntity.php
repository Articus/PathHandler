<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class UnprocessableEntity extends HttpCode
{
	public function __construct(mixed $validationResult, null|Throwable $previous = null)
	{
		parent::__construct(422, 'Unprocessable entity', $validationResult, $previous);
	}
}
