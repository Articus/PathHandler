<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Operation;
use Psr\Http\Message\ServerRequestInterface;

class PostWithPriorityProducers implements Operation\PostInterface
{
	/**
	 * @PHA\Producer(name="TestLow", mediaType="application/json", options={"test":"produce"})
	 * @PHA\Producer(name="Test", mediaType="application/json", options={"test":"produce"}, priority=2)
	 * @param ServerRequestInterface $request
	 * @return array
	 */
	public function handlePost(ServerRequestInterface $request)
	{
		return ['test' => 'payload'];
	}
}