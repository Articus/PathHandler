<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Operation;
use Psr\Http\Message\ServerRequestInterface;

class PostWithPriorityConsumers implements Operation\PostInterface
{
	/**
	 * @PHA\Consumer(name="TestLow", mediaType="application/json", options={"test":"consume"})
	 * @PHA\Consumer(name="Test", mediaType="application/json", options={"test":"consume"}, priority=2)
	 * @param ServerRequestInterface $request
	 * @return array
	 */
	public function handlePost(ServerRequestInterface $request)
	{
		return ['test' => 'payload'];
	}
}