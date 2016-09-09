<?php
namespace Articus\PathHandler\Exception;

use Exception;

class PermanentRedirect extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct($location, Exception $previous = null)
	{
		parent::__construct(308, 'Permanent redirect', null, $previous);
		$this->location = $location;
	}
}