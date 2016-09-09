<?php
namespace Articus\PathHandler\Exception;

use Exception;

class BadRequest extends HttpCode
{
	public function __construct($reason = 'Bad request', Exception $previous = null)
	{
		parent::__construct(400, $reason, null, $previous);
	}
}