<?php
namespace Articus\PathHandler\Exception;

use Exception;

class NotFound extends HttpCode
{
	public function __construct(Exception $previous = null)
	{
		parent::__construct(404, 'Not found', null, $previous);
	}
}