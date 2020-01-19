<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use Articus\DataTransfer\Service as DTService;

/**
 * JSON producer extension that simplifies complex data structures with Articus\DataTransfer\Service before encoding
 */
class Transfer extends Json
{
	/**
	 * @var DTService
	 */
	protected $dtService;

	/**
	 * @var string
	 */
	protected $subset;

	/**
	 * @param callable $streamFactory
	 * @param DTService $dtService
	 * @param string $subset
	 */
	public function __construct(callable $streamFactory, DTService $dtService, string $subset)
	{
		parent::__construct($streamFactory);
		$this->dtService = $dtService;
		$this->subset = $subset;
	}

	/**
	 * @inheritdoc
	 */
	protected function stringify($objectOrArray): string
	{
		$data = $this->transfer($objectOrArray);
		return parent::stringify($data);
	}

	/**
	 * Tries to transfer data from specified object or array to multi-dimensional scalar array
	 * @param mixed $objectOrArray
	 * @return mixed transfered data
	 */
	protected function transfer($objectOrArray)
	{
		$data = null;
		if (\is_object($objectOrArray))
		{
			$data = $this->dtService->extractFromTypedData($objectOrArray, $this->subset);
		}
		elseif (\is_array($objectOrArray))
		{
			$data = [];
			foreach ($objectOrArray as $index => $itemObjectOrArray)
			{
				$data[$index] = $this->transfer($itemObjectOrArray);
			}
		}
		else
		{
			$data = $objectOrArray;
		}
		return $data;
	}
}