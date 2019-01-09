<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class MethodNotAllowed extends HttpCode
{
	public function __construct(\Exception $previous = null)
	{
		parent::__construct(405, 'Method not allowed', null, $previous);
	}
}