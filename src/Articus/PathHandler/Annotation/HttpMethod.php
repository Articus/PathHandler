<?php
declare(strict_types=1);

namespace Articus\PathHandler\Annotation;

/**
 * Annotation to declare that marked handler class method should be used to handle requests with specified HTTP method
 * @Annotation
 * @Target({"METHOD"})
 */
class HttpMethod
{
	/**
	 * Name of HTTP method
	 * @var string
	 */
	protected $value;

	public function __construct(array $values)
	{
		if (empty($values['value']))
		{
			throw new \LogicException('HTTP method value is required.');
		}
		elseif (!\is_string($values['value']))
		{
			throw new \LogicException(\sprintf('HTTP method value should be string, not %s.', \gettype($values['value'])));
		}
		$this->value = $values['value'];
	}

	public function getValue(): string
	{
		return $this->value;
	}
}