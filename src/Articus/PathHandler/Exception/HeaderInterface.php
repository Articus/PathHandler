<?php

namespace Articus\PathHandler\Exception;

/**
 * Interface for HttpCode exceptions that need to add headers to response
 */
interface HeaderInterface
{
	/**
	 * Should yield header name => header value
	 * @return \Generator
	 */
	public function getHeaders();
}