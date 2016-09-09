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
					$this->consumers[] = $annotation;
					break;
				case ($annotation instanceof Annotation\Attribute):
					$this->attributes[] = $annotation;
					break;
				case ($annotation instanceof Annotation\Producer):
					$this->producers[] = $annotation;
					break;
			}
		}
	}
}