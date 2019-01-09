<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

/**
 * Simple producer that provides JSON encoding
 */
class Json extends AbstractProducer
{
	/**
	 * @inheritdoc
	 */
	protected function stringify($data): string
	{
		$result = \json_encode($data);
		if ($result === false)
		{
			throw new \InvalidArgumentException(\sprintf(
				'Failed to encode %s to JSON.', \is_object($data) ? \get_class($data) : \gettype($data)
			));
		}
		return $result;
	}
}