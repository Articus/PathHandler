<?php
namespace Test\PathHandler;

use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Diactoros\Uri;

trait RequestTrait
{
	protected function createRequest($method, $path, $headers, $query, $body)
	{
		$uri = new Uri();
		$uri = $uri
			->withPath($path)
			->withQuery(http_build_query($query))
		;
		$bodyStream = new Stream('php://memory', 'wb+');
		$bodyStream->write($body);
		$bodyStream->rewind();
		return new ServerRequest([], [], $uri, $method, $bodyStream, $headers, [], $query);
	}

}