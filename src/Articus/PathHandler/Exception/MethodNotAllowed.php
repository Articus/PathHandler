<?php
namespace Articus\PathHandler\Exception;

use Exception;

class MethodNotAllowed extends HttpCode
{
	public function __construct(Exception $previous = null)
	{
		parent::__construct(405, 'Method not allowed', null, $previous);
	}
}