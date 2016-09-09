<?php
namespace Articus\PathHandler\Exception;

use Exception;

class TemporaryRedirect extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct($location, Exception $previous = null)
	{
		parent::__construct(307, 'Temporary redirect', null, $previous);
		$this->location = $location;
	}
}