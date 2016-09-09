<?php
namespace Articus\PathHandler\Exception;

use Exception;

/**
 * Custom exception class for situation when specific HTTP code should be returned to client immediately
 */
class HttpCode extends \Exception
{
	/**
	 * Additional data that can clarify code reason
	 * @var mixed
	 */
	protected $payload;

	public function __construct($code, $reason, $payload = null, Exception $previous = null)
	{
		parent::__construct($reason, $code, $previous);
		$this->payload = $payload;
	}

	/**
	 * @return mixed
	 */
	public function getPayload()
	{
		return $this->payload;
	}
}