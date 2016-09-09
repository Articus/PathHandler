<?php

namespace Articus\PathHandler\Producer;

use Zend\Diactoros\Stream;

/**
 * Base class for producers that somehow transform data to string
 */
abstract class AbstractProducer implements ProducerInterface
{
	/**
	 * @inheritdoc
	 */
	public function assemble($data)
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
	abstract protected function stringify($data);
}