<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use Articus\DataTransfer\Service as DTService;
use Psr\Http\Message\StreamInterface;
use function is_array;
use function is_object;

/**
 * Producer decorator that simplifies complex data structures with \Articus\DataTransfer\Service before passing it to underlying producer for assembling
 * @see Options\Transfer for details
 */
class Transfer implements ProducerInterface
{
	public function __construct(
		protected ProducerInterface $producer,
		protected DTService $dt,
		protected string $subset
	)
	{
	}

	/**
	 * @inheritdoc
	 */
	public function assemble(mixed $data): null|StreamInterface
	{
		return $this->producer->assemble($this->transfer($data));
	}

	/**
	 * Tries to transfer data from specified object or array to multidimensional scalar array
	 */
	protected function transfer(mixed $data): mixed
	{
		$result = null;
		if (is_object($data))
		{
			$result = $this->dt->extractFromTypedData($data, $this->subset);
		}
		elseif (is_array($data))
		{
			$result = [];
			foreach ($data as $itemIndex => $itemData)
			{
				$result[$itemIndex] = $this->transfer($itemData);
			}
		}
		else
		{
			$result = $data;
		}
		return $result;
	}
}
