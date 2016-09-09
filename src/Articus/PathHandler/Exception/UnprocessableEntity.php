<?php
namespace Articus\PathHandler\Exception;

use Exception;

class UnprocessableEntity extends HttpCode
{
	public function __construct(array $validatorMessages, Exception $previous = null)
	{
		parent::__construct(422, 'Unprocessable entity', $validatorMessages, $previous);
	}
}