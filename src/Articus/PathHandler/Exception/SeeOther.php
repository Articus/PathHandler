<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class SeeOther extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct(string $location, \Exception $previous = null)
	{
		parent::__construct(303, 'See other', null, $previous);
		$this->location = $location;
	}
}