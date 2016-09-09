<?php
namespace Articus\PathHandler\Operation;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for handlers that can handle PATCH request
 */
interface PatchInterface
{
	public function handlePatch(ServerRequestInterface $request);
}