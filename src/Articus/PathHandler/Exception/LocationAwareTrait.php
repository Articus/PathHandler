<?php

namespace Articus\PathHandler\Exception;

/**
 * Trait for exceptions that set "Location" header
 */
trait LocationAwareTrait
{
	protected $location;

	/**
	 * @inheritdoc
	 */
	public function getHeaders()
	{
		yield 'Location' => $this->location;
	}
}