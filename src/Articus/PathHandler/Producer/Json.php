<?php
namespace Articus\PathHandler\Producer;

/**
 * Simple producer that provides JSON encoding
 */
class Json extends AbstractProducer
{
	/**
	 * @inheritdoc
	 */
	protected function stringify($data)
	{
		$result = json_encode($data);
		if ($result === false)
		{
			throw new \InvalidArgumentException(
				sprintf('Failed to encode %s to JSON.', is_object($data)? get_class($data): gettype($data))
			);
		}
		return $result;
	}
}