<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer;

use Articus\PathHandler\Exception;
use Psr\Http\Message\StreamInterface;

/**
 * Consumer that presumes that request body is a valid JSON string
 */
class Json implements ConsumerInterface
{
	/**
	 * @inheritdoc
	 * @throws Exception\BadRequest
	 */
	public function parse(StreamInterface $body, $preParsedBody, string $mediaType, array $parameters)
	{
		//TODO allow to pass decoding options via parameters
		$result = \json_decode($body->getContents(), true);
		if (($result === null) && (\json_last_error() !== \JSON_ERROR_NONE))
		{
			throw new Exception\BadRequest('Malformed JSON: failed to decode');
		}
		elseif (!(($result === null) || \is_array($result)))
		{
			throw new Exception\BadRequest('Malformed JSON: expecting null, array or object');
		}
		return $result;
	}
}