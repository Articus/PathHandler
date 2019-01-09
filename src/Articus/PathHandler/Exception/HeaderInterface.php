<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

/**
 * Interface for HttpCode exceptions that need to add headers to response
 */
interface HeaderInterface
{
	/**
	 * Should yield header name => header value(s)
	 * @return \Generator
	 */
	public function getHeaders(): \Generator;
}