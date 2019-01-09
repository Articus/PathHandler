<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class TemporaryRedirect extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct(string $location, \Exception $previous = null)
	{
		parent::__construct(307, 'Temporary redirect', null, $previous);
		$this->location = $location;
	}
}