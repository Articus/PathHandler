<?php
declare(strict_types=1);

namespace Articus\PathHandler\Producer;

use Articus\DataTransfer\Mapper\MapperInterface;
use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler\Exception\UnprocessableEntity;

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
	 * @var null|callable|MapperInterface
	 */
	protected $mapper;

	/**
	 * @param DTService $dtService
	 * @param null|callable|MapperInterface $mapper
	 */
	public function __construct(DTService $dtService, $mapper = null)
	{
		$this->dtService = $dtService;
		$this->mapper = $mapper;
	}

	/**
	 * @inheritdoc
	 */
	protected function stringify($objectOrArray): string
	{
		[$data, $errors] = $this->transfer($objectOrArray);
		if (!empty($errors))
		{
			throw new \InvalidArgumentException('Failed to transfer data.', 0, new UnprocessableEntity($errors));
		}
		return parent::stringify($data);
	}

	/**
	 * Tries to transfer data from specified object or array to multi-dimensional scalar array
	 * @param mixed $objectOrArray
	 * @return array tuple (<transfered data> , <errors encountered during transfer>)
	 */
	protected function transfer($objectOrArray): array
	{
		$data = [];
		$errors = [];
		if (\is_object($objectOrArray))
		{
			$errors = $this->dtService->transfer($objectOrArray, $data, $this->mapper);
		}
		elseif (\is_array($objectOrArray))
		{
			foreach ($objectOrArray as $index => $itemObjectOrArray)
			{
				[$data[$index], $itemErrors] = $this->transfer($itemObjectOrArray);
				if (!empty($itemErrors))
				{
					$errors[$index] = $itemErrors;
				}
			}
		}
		else
		{
			$data = $objectOrArray;
		}
		return [$data, $errors];
	}
}