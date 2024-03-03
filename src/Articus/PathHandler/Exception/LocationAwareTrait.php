<?php
declare(strict_types=1);

namespace Articus\PathHandler\Exception;

/**
 * Trait for exceptions that set "Location" header
 */
trait LocationAwareTrait
{
	protected string $location;

	/**
	 * @inheritdoc
	 */
	public function getHeaders(): iterable
	{
		yield 'Location' => $this->location;
	}
}
