<?php
declare(strict_types=1);

namespace Articus\PathHandler\Consumer;

use Articus\DataTransfer\Exception as DTException;
use Articus\PathHandler\Exception;
use Psr\Http\Message\StreamInterface;
use function is_array;
use function is_object;
use function json_decode;
use function json_last_error;
use const JSON_ERROR_NONE;

/**
 * Consumer that presumes that request body is a valid JSON string containing null, array or object
 * @see Options\Json for details.
 */
class Json implements ConsumerInterface
{
	public function __construct(
		protected int $decodeFlags,
		protected int $depth
	)
	{
	}

	/**
	 * @inheritdoc
	 * @throws Exception\BadRequest
	 * @throws Exception\UnprocessableEntity
	 */
	public function parse(StreamInterface $body, null|array|object $preParsedBody, string $mediaType, array $parameters): null|array|object
	{
		$result = json_decode($body->getContents(), depth: $this->depth, flags: $this->decodeFlags);
		if (($result === null) && (json_last_error() !== JSON_ERROR_NONE))
		{
			throw new Exception\BadRequest('Malformed JSON: failed to decode');
		}
		if (!(($result === null) || is_array($result) || is_object($result)))
		{
			throw new Exception\UnprocessableEntity(DTException\InvalidData::DEFAULT_VIOLATION);
		}
		return $result;
	}
}
