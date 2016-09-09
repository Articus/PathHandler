<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Operation;
use Psr\Http\Message\ServerRequestInterface;

class PostWithSeveralConsumers implements Operation\PostInterface
{
	/**
	 * @PHA\Consumer(name="Test", mediaType="application/json", options={"test":"consume"})
	 * @PHA\Consumer(name="Test", mediaType="text/html", options={"test":"consume"})
	 * @param ServerRequestInterface $request
	 * @return array
	 */
	public function handlePost(ServerRequestInterface $request)
	{
		return ['test' => 'payload'];
	}
}