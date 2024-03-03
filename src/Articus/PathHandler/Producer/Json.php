<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use function get_debug_type;
use function json_encode;
use function sprintf;

/**
 * Simple producer that provides JSON encoding
 * @see Options\Json for details
 */
class Json implements ProducerInterface
{
	public function __construct(
		protected StreamFactoryInterface $streamFactory,
		protected int $encodeFlags,
		protected int $depth
	)
	{
	}

	/**
	 * @inheritdoc
	 */
	public function assemble(mixed $data): null|StreamInterface
	{
		$content = json_encode($data, $this->encodeFlags, $this->depth);
		if ($content === false)
		{
			throw new InvalidArgumentException(sprintf('Failed to encode %s to JSON.', get_debug_type($data)));
		}
		return $this->streamFactory->createStream($content);
	}
}
