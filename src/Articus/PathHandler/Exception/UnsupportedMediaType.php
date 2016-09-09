<?php
namespace Articus\PathHandler\Exception;

use Exception;

class UnsupportedMediaType extends HttpCode
{
	public function __construct(Exception $previous = null)
	{
		parent::__construct(415, 'Unsupported media type', null, $previous);
	}
}