<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class NotFound extends HttpCode
{
	public function __construct(\Exception $previous = null)
	{
		parent::__construct(404, 'Not found', null, $previous);
	}
}