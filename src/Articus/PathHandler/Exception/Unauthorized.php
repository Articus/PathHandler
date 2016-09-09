<?php
namespace Articus\PathHandler\Exception;

use Exception;

class Unauthorized extends HttpCode
{
	public function __construct($reason = 'Unauthorized', Exception $previous = null)
	{
		parent::__construct(401, $reason, null, $previous);
	}
}