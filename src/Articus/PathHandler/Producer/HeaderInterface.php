<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

/**
 * Interface for producers that need to add headers to response
 */
interface HeaderInterface
{
	/**
	 * Should return or yield headers to set
	 * @param mixed $data
	 * @return iterable<string, string> map "header name" -> "header value"
	 */
	public function assembleHeaders(mixed $data): iterable;
}
