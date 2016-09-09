<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;
use Articus\PathHandler\Operation;
use Psr\Http\Message\ServerRequestInterface;

class PostWithDefaultPriorityAttributes implements Operation\PostInterface
{
	/**
	 * @PHA\Attribute(name="TestLow", options={"test":"attribute"})
	 * @PHA\Attribute(name="Test", options={"test":"attribute"})
	 * @param ServerRequestInterface $request
	 * @return array
	 */
	public function handlePost(ServerRequestInterface $request)
	{
		return ['test' => 'payload'];
	}
}