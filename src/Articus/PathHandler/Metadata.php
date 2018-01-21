<?php

namespace Articus\PathHandler;

use Zend\Stdlib\FastPriorityQueue;

/**
 * Class for storing metadata associated with each method to handle HTTP request
 */
class Metadata
{
	/**
	 * List of consumers
	 * @var Annotation\Consumer[]|FastPriorityQueue
	 */
	public $consumers;

	/**
	 * List of producers
	 * @var Annotation\Producer[]|FastPriorityQueue
	 */
	public $producers;

	/**
	 * List of attributes
	 * @var Annotation\Attribute[]|FastPriorityQueue
	 */
	public $attributes;

	/**
	 * Metadata constructor.
	 */
	public function __construct()
	{
		$this->consumers = new FastPriorityQueue();
		$this->attributes = new FastPriorityQueue();
		$this->producers = new FastPriorityQueue();
	}

	/**
	 * @inheritdoc
	 */
	public function __clone()
	{
		$this->consumers = clone $this->consumers;
		$this->attributes = clone $this->attributes;
		$this->producers = clone $this->producers;
	}

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
					$this->consumers->insert($annotation, $annotation->priority);
					break;
				case ($annotation instanceof Annotation\Attribute):
					$this->attributes->insert($annotation, $annotation->priority);
					break;
				case ($annotation instanceof Annotation\Producer):
					$this->producers->insert($annotation, $annotation->priority);
					break;
			}
		}
	}
}