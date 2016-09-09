<?php

namespace Articus\PathHandler\Consumer;

use Articus\PathHandler\Exception;
use Psr\Http\Message\StreamInterface;

/**
 * Consumer that presumes that request body is a valid JSON string
 */
class Json implements ConsumerInterface
{
	public function parse(StreamInterface $body, $preParsedBody, $mediaType, array $parameters)
	{
		$result = json_decode($body->getContents(), true);
		if (($result === null) && ($result !== 'null'))
		{
			throw new Exception\BadRequest('Malformed JSON');
		}
		return $result;
	}
}