<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

class Text extends AbstractProducer
{
	protected function stringify($data): string
	{
		if (!(($data === null) || \is_string($data) || ($data instanceof \Stringable) || (\is_object($data) && \method_exists($data, '__toString'))))
		{
			throw new \InvalidArgumentException(\sprintf(
				'Failed to convert %s to string.', \is_object($data) ? \get_class($data) : \gettype($data)
			));
		}
		return (string) $data;
	}
}
