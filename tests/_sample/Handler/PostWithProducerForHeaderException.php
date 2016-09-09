<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Operation;
use Psr\Http\Message\ServerRequestInterface;
use Test\PathHandler\Sample\Exception\WithHeader;

class PostWithProducerForHeaderException implements Operation\PostInterface
{
	/**
	 * @PHA\Producer(name="Test", mediaType="application/json", options={"test":"produce"})
	 * @param ServerRequestInterface $request
	 * @return array
	 */
	public function handlePost(ServerRequestInterface $request)
	{
		throw new WithHeader(123, 'Test reason', ['test' => 'payload']);
	}
}