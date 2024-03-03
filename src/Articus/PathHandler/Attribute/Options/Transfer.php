<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Options;

use Articus\PathHandler\Attribute;
use LogicException;
use function class_exists;
use function sprintf;

class Transfer
{
	/**
	 * Source of data to transfer, should be one of \Articus\PathHandler\Attribute\Transfer::SOURCE_* constants
	 */
	public string $source = Attribute\Transfer::SOURCE_POST;

	/**
	 * Class name for hydrated object
	 * @var class-string
	 */
	public string $type;

	/**
	 * Name of the data subset that should be transferred
	 */
	public string $subset = '';

	/**
	 * Name of the request attribute to store hydrated object
	 */
	public string $objectAttr = 'object';

	/**
	 * Name of the service that should be used to instanciate new object if request does not contain one.
	 * If it is null type constructor is called without arguments.
	 * Service is invoked with type name and either request object or specified request attributes values
	 * so callable(class-string, mixed...): object is expected.
	 */
	public null|string $instanciator = null;

	/**
	 * Names of request attributes that should be passed to instanciator to create new object.
	 * If it is empty whole request is passed.
	 * @var string[]
	 */
	public array $instanciatorArgAttrs = [];

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
						case Attribute\Transfer::SOURCE_ATTRIBUTE:
							$this->source = $value;
							break;
						default:
							throw new LogicException(sprintf('Value "%s" for option "source" is not supported.', $value));
					}
					break;
				case 'type':
					if (!class_exists($value))
					{
						throw new LogicException(sprintf('Option "type" should be a valid class name, not "%s".', $value));
					}
					$this->type = $value;
					break;
				case 'subset':
					$this->subset = $value;
					break;
				case 'objectAttr':
				case 'object_attr':
					$this->objectAttr = $value;
					break;
				case 'instanciator':
					$this->instanciator = $value;
					break;
				case 'instanciatorArgAttrs':
				case 'instanciator_arg_attrs':
					$this->instanciatorArgAttrs = $value;
					break;
				case 'errorAttr':
				case 'error_attr':
					$this->errorAttr = $value;
					break;
			}
		}
	}
}
