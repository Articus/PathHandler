<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer;

use Psr\Http\Message\StreamInterface;

/**
 * Consumer that relies on internal mechanism of request body parsing
 */
class Internal implements ConsumerInterface
{
	public function parse(StreamInterface $body, $preParsedBody, string $mediaType, array $parameters)
	{
		return $preParsedBody;
	}
}