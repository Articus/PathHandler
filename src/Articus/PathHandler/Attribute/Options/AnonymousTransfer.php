<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Options;

use Articus\PathHandler\Attribute;
use LogicException;
use function sprintf;

class AnonymousTransfer
{
	/**
	 * Source of data to transfer, should be one of \Articus\PathHandler\Attribute\Transfer::SOURCE_* constants
	 */
	public string $source = Attribute\Transfer::SOURCE_POST;

	/**
	 * Name of the request attribute to store hydrated object
	 */
	public string $objectAttr = 'object';

	/**
	 * Declaration of the strategy to transfer data from source
	 * @var array{0: string, 1: array} tuple ("name for data transfer strategy plugin manager", "options for data transfer strategy plugin manager")
	 */
	public array $strategy;

	/**
	 * Declaration of the validator to transfer data from source
	 * @var array{0: string, 1: array} tuple ("name for data transfer validator plugin manager", "options for data transfer validator plugin manager")
	 */
	public array $validator;

	/**
	 * Name of the request attribute to store validation errors.
	 * If it is empty \Articus\PathHandler\Exception\UnprocessableEntity is raised.
	 */
	public null|string $errorAttr = null;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'source':
					switch ($value)
					{
						case Attribute\Transfer::SOURCE_GET:
						case Attribute\Transfer::SOURCE_POST:
						case Attribute\Transfer::SOURCE_ROUTE:
						case Attribute\Transfer::SOURCE_HEADER:
							$this->source = $value;
							break;
						default:
							throw new LogicException(sprintf('Value "%s" for option "source" is not supported.', $value));
					}
					break;
				case 'strategy':
					$this->strategy = $value;
					break;
				case 'validator':
					$this->validator = $value;
					break;
				case 'objectAttr':
				case 'object_attr':
					$this->objectAttr = $value;
					break;
				case 'errorAttr':
				case 'error_attr':
					$this->errorAttr = $value;
					break;
			}
		}
	}
}
