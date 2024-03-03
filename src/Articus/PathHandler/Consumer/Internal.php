<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer;

use Psr\Http\Message\StreamInterface;

/**
 * Consumer that relies on internal mechanism of request body parsing
 */
class Internal implements ConsumerInterface
{
	/**
	 * @inheritdoc
	 */
	public function parse(StreamInterface $body, null|array|object $preParsedBody, string $mediaType, array $parameters): null|array|object
	{
		return $preParsedBody;
	}
}
