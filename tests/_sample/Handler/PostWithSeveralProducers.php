<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Operation;
use Psr\Http\Message\ServerRequestInterface;

class PostWithSeveralProducers implements Operation\PostInterface
{
	/**
	 * @PHA\Producer(name="Test", mediaType="application/json", options={"test":"produce"})
	 * @PHA\Producer(name="Test", mediaType="text/html", options={"test":"produce"})
	 * @param ServerRequestInterface $request
	 * @return array
	 */
	public function handlePost(ServerRequestInterface $request)
	{
		return ['test' => 'payload'];
	}
}