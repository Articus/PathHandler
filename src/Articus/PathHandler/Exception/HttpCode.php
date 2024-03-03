<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

use Exception;
use Throwable;

/**
 * Custom exception class for situation when specific HTTP code should be returned to client immediately
 */
class HttpCode extends Exception
{
	/**
	 * Additional data that can clarify code reason
	 */
	protected mixed $payload;

	public function __construct(int $code, string $reason, mixed $payload = null, null|Throwable $previous = null)
	{
		parent::__construct($reason, $code, $previous);
		$this->payload = $payload;
	}

	public function getPayload(): mixed
	{
		return $this->payload;
	}
}
