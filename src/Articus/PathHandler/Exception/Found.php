<?php
namespace Articus\PathHandler\Exception;

use Exception;

class Found extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct($location, Exception $previous = null)
	{
		parent::__construct(302, 'Found', null, $previous);
		$this->location = $location;
	}
}