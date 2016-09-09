<?php
namespace Articus\PathHandler\Attribute\Options;

use Zend\Stdlib\AbstractOptions;

class Transfer extends AbstractOptions
{
	/**
	 * @var string
	 */
	protected $source = \Articus\PathHandler\Attribute\Transfer::SOURCE_POST;

	/**
	 * Class name for hydrated object (should have constructor with no parameters)
	 * @var string
	 */
	protected $type;

	/**
	 * Name of the request attribute to store hydrated object
	 * @var string
	 */
	protected $objectAttr = 'object';

	/**
	 * Name of the request attribute to store validation errors, if empty Exception\UnprocessableEntity is raised
	 * @var string
	 */
	protected $errorAttr = null;

	/**
	 * @return string
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param string $source
	 * @return self
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return self
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getObjectAttr()
	{
		return $this->objectAttr;
	}

	/**
	 * @param string $objectAttr
	 * @return self
	 */
	public function setObjectAttr($objectAttr)
	{
		$this->objectAttr = $objectAttr;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getErrorAttr()
	{
		return $this->errorAttr;
	}

	/**
	 * @param string $errorAttr
	 * @return self
	 */
	public function setErrorAttr($errorAttr)
	{
		$this->errorAttr = $errorAttr;
		return $this;
	}
}