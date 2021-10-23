<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use Psr\Http\Message\StreamInterface;

/**
 * Base class for producers that somehow transform data to string
 */
abstract class AbstractProducer implements ProducerInterface
{
	/**
	 * @var callable
	 */
	protected $streamFactory;

	/**
	 * AbstractProducer constructor.
	 * @param callable $streamFactory
	 */
	public function __construct(callable $streamFactory)
	{
		$this->streamFactory = $streamFactory;
	}

	/**
	 * @inheritdoc
	 */
	public function assemble($data): ?StreamInterface
	{
		$result = $this->createStream();
		$result->write($this->stringify($data));
		$result->rewind();
		return $result;
	}

	/**
	 * Creates new stream.
	 * Using separate method just because there is no return type declaration for callable.
	 * @return StreamInterface
	 */
	protected function createStream(): StreamInterface
	{
		return ($this->streamFactory)();
	}

	/**
	 * Transforms supplied data to string
	 * @param mixed $data
	 * @return string
	 */
	abstract protected function stringify($data): string;
}