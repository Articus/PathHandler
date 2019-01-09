<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

class NotAcceptable extends HttpCode
{
	public function __construct(\Exception $previous = null)
	{
		parent::__construct(406, 'Not acceptable', null, $previous);
	}
}