<?php
namespace Articus\PathHandler\Exception;

use Exception;

class NotAcceptable extends HttpCode
{
	public function __construct(Exception $previous = null)
	{
		parent::__construct(406, 'Not acceptable', null, $previous);
	}
}