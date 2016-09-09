<?php
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
	 * @var callable|MapperInterface
	 */
	protected $mapper;
	/**
	 * @param DTService $dtService
	 */
	public function __construct(DTService $dtService, $mapper = null)
	{
		$this->dtService = $dtService;
		$this->mapper = $mapper;
	}

	/**
	 * @inheritdoc
	 */
	protected function stringify($objectOrArray)
	{
		$array = [];
		$errors = [];
		if (is_object($objectOrArray))
		{
			$errors = $this->dtService->transfer($objectOrArray, $array, $this->mapper);
		}
		elseif (is_array($objectOrArray))
		{
			foreach ($objectOrArray as $index => $object)
			{
				$item = [];
				if (is_object($object))
				{
					$itemErrors = $this->dtService->transfer($object, $item, $this->mapper);
					if (!empty($itemErrors))
					{
						$errors[$index] = $itemErrors;
					}
				}
				else
				{
					$item = $object;
				}
				$array[$index] = $item;
			}
		}
		if (!empty($errors))
		{
			throw new UnprocessableEntity($errors);
		}
		return parent::stringify($array);
	}
}