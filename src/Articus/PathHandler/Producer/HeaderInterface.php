<?php

namespace Articus\PathHandler\Producer;

/**
 * Interface for producers that need to add headers to response
 */
interface HeaderInterface
{
	/**
	 * Should yield header name => header value
	 * @param mixed $data
	 * @return \Generator
	 */
	public function assembleHeaders($data);
}