<?php
namespace Articus\PathHandler\Operation;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for handlers that can handle PUT request
 */
interface PutInterface
{
	public function handlePut(ServerRequestInterface $request);
}