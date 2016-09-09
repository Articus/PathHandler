<?php

namespace Articus\PathHandler;

/**
 * Class for storing metadata associated with each method to handle HTTP request
 */
class Metadata
{
	/**
	 * List of consumers
	 * @var Annotation\Consumer[]
	 */
	public $consumers = [];

	/**
	 * List of producers
	 * @var Annotation\Producer[]
	 */
	public $producers = [];

	/**
	 * List of attributes
	 * @var Annotation\Attribute[]
	 */
	public $attributes = [];

	/**
	 * Adds eligible annotations from provided list to metadata
	 * @param array $annotations
	 */
	public function addAnnotations(array $annotations)
	{
		foreach ($annotations as $annotation)
		{
			switch (true)
			{
				case ($annotation instanceof Annotation\Consumer):
					$index = self::generateNewIndex(count($this->consumers), $annotation->priority);
					$this->consumers[$index] = $annotation;
					break;
				case ($annotation instanceof Annotation\Attribute):
					$index = self::generateNewIndex(count($this->attributes), $annotation->priority);
					$this->attributes[$index] = $annotation;
					break;
				case ($annotation instanceof Annotation\Producer):
					$index = self::generateNewIndex(count($this->producers), $annotation->priority);
					$this->producers[$index] = $annotation;
					break;
			}
		}
	}

	/**
	 * Generate index for lists of consumers, attributes and producers in a way that will allow to sort them by key.
	 * Initially sorting was done only by priority, but PHP 5.6 and PHP 7 order equal values differently.
	 * That is why this is workaround was made to ensure identical behaviour.
	 * @param int $count
	 * @param int $priority
	 */
	public static function generateNewIndex($count, $priority = 1)
	{
		$maxCount = 1000;
		if ($count >= $maxCount)
		{
			throw new \LogicException('Impossibly huge number of elements in list.');
		}
		return -($priority * $maxCount + $count);
	}
}