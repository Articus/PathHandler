<?php
namespace Articus\PathHandler\Operation;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for handlers that can handle POST request
 */
interface PostInterface
{
	public function handlePost(ServerRequestInterface $request);
}