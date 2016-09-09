<?php

namespace Articus\PathHandler\Operation;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for handlers that can handle DELETE request
 */
interface DeleteInterface
{
	public function handleDelete(ServerRequestInterface $request);
}