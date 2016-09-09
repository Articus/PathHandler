<?php
namespace Articus\PathHandler\Exception;

use Exception;

class MovedPermanently extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct($location, Exception $previous = null)
	{
		parent::__construct(301, 'Moved permanently', null, $previous);
		$this->location = $location;
	}
}