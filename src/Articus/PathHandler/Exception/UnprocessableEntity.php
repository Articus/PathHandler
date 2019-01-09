<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class UnprocessableEntity extends HttpCode
{
	public function __construct($validationResult, \Exception $previous = null)
	{
		parent::__construct(422, 'Unprocessable entity', $validationResult, $previous);
	}
}