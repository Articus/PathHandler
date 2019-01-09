<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class UnsupportedMediaType extends HttpCode
{
	public function __construct(\Exception $previous = null)
	{
		parent::__construct(415, 'Unsupported media type', null, $previous);
	}
}