<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Stringable;
use function get_debug_type;
use function is_object;
use function is_string;
use function method_exists;
use function sprintf;

class Text implements ProducerInterface
{
	public function __construct(
		protected StreamFactoryInterface $streamFactory
	)
	{
	}

	/**
	 * @inheritdoc
	 */
	public function assemble(mixed $data): null|StreamInterface
	{
		if (!(($data === null) || is_string($data) || ($data instanceof Stringable) || (is_object($data) && method_exists($data, '__toString'))))
		{
			throw new InvalidArgumentException(sprintf('Failed to convert %s to string.', get_debug_type($data)));
		}
		return $this->streamFactory->createStream((string) $data);
	}
}
