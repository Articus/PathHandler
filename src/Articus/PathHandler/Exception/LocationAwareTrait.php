<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

/**
 * Trait for exceptions that set "Location" header
 */
trait LocationAwareTrait
{
	/**
	 * @var string
	 */
	protected $location;

	/**
	 * @inheritdoc
	 */
	public function getHeaders(): \Generator
	{
		yield 'Location' => $this->location;
	}
}