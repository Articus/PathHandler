<?php
namespace Articus\PathHandler\Consumer;

use Psr\Http\Message\StreamInterface;

/**
 * Interface for consumers - services that are used to parse request body
 */
interface ConsumerInterface
{
	/**
	 * Parses request body
	 * @param StreamInterface $body - content of the request body
	 * @param $preParsedBody - content of the request body that was parsed before the consumer (for some content types it is done internally)
	 * @param string $mediaType - media type supplied in Content-Type header of the request
	 * @param array $parameters - parameters supplied in Content-Type header of the request
	 * @return array|null|object
	 */
	public function parse(StreamInterface $body, $preParsedBody, $mediaType, array $parameters);
}