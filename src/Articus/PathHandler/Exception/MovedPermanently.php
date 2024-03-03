<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class MovedPermanently extends HttpCode implements HeaderInterface
{
	use LocationAwareTrait;

	public function __construct(string $location, null|Throwable $previous = null)
	{
		parent::__construct(301, 'Moved permanently', null, $previous);
		$this->location = $location;
	}
}
