<?php
declare(strict_types=1);

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
	public function getSource(): string
	{
		return $this->source;
	}

	/**
	 * @param string $source
	 */
	public function setSource(string $source): void
	{
		$this->source = $source;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType(string $type): void
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getObjectAttr(): string
	{
		return $this->objectAttr;
	}

	/**
	 * @param string $objectAttr
	 */
	public function setObjectAttr(string $objectAttr): void
	{
		$this->objectAttr = $objectAttr;
	}

	/**
	 * @return string
	 */
	public function getErrorAttr(): ?string
	{
		return $this->errorAttr;
	}

	/**
	 * @param string $errorAttr
	 */
	public function setErrorAttr(?string $errorAttr): void
	{
		$this->errorAttr = $errorAttr;
	}
}