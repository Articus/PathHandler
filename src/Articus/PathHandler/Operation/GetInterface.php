<?php
namespace Articus\PathHandler\Operation;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for handlers that can handle GET request
 */
interface GetInterface
{
	public function handleGet(ServerRequestInterface $request);
}