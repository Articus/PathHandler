<?php
namespace Articus\PathHandler\Exception;

use Exception;

class Forbidden extends HttpCode
{
	public function __construct($reason = 'Forbidden', Exception $previous = null)
	{
		parent::__construct(403, $reason, null, $previous);
	}
}