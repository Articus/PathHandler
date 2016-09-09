<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Exception\HttpCode;
use Articus\PathHandler\Operation;
use Psr\Http\Message\ServerRequestInterface;

class PostWithProducerForException implements Operation\PostInterface
{
	/**
	 * @PHA\Producer(name="Test", mediaType="application/json", options={"test":"produce"})
	 * @param ServerRequestInterface $request
	 * @return array
	 */
	public function handlePost(ServerRequestInterface $request)
	{
		throw new HttpCode(123, 'Test reason', ['test' => 'payload']);
	}
}