<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Throwable;

class UnsupportedMediaType extends HttpCode
{
	public function __construct(null|Throwable $previous = null)
	{
		parent::__construct(415, 'Unsupported media type', null, $previous);
	}
}
