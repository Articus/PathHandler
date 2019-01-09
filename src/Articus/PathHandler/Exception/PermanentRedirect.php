<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class PermanentRedirect extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct(string $location, \Exception $previous = null)
	{
		parent::__construct(308, 'Permanent redirect', null, $previous);
		$this->location = $location;
	}
}