<?php
namespace Articus\PathHandler\Exception;

use Exception;

class SeeOther extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct($location, Exception $previous = null)
	{
		parent::__construct(303, 'See other', null, $previous);
		$this->location = $location;
	}
}