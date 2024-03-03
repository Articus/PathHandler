<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

/**
 * Interface for HttpCode exceptions that need to add headers to response
 */
interface HeaderInterface
{
	/**
	 * Should return or yield additional headers
	 * @return iterable<string, string|string[]> map "header name" -> "header value(s)"
	 */
	public function getHeaders(): iterable;
}
