<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Stream;

/**
 * Base class for producers that somehow transform data to string
 */
abstract class AbstractProducer implements ProducerInterface
{
	/**
	 * @inheritdoc
	 */
	public function assemble($data): ?StreamInterface
	{
		$result = null;
		if ($data !== null)
		{
			$result = new Stream('php://temp', 'wb+');
			$result->write($this->stringify($data));
			$result->rewind();
		}

		return $result;
	}

	/**
	 * Transforms supplied data to string
	 * @param mixed $data
	 * @return string
	 */
	abstract protected function stringify($data): string;
}