<?php
namespace Articus\PathHandler\Exception;

use Exception;

class MovedTemporarily extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct($location, Exception $previous = null)
	{
		parent::__construct(302, 'Moved temporarily', null, $previous);
		$this->location = $location;
	}
}