<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Options;

use Articus\PathHandler\Attribute;

class Transfer
{
	/**
	 * Source of data to transfer
	 * @var string
	 */
	public $source = Attribute\Transfer::SOURCE_POST;

	/**
	 * Class name for hydrated object
	 * @var string
	 */
	public $type;

	/**
	 * Name of the data subset that should be transferred
	 * @var string
	 */
	public $subset = '';

	/**
	 * Name of the request attribute to store hydrated object
	 * @var string
	 */
	public $objectAttr = 'object';

	/**
	 * Name of the service that should be used to instanciate new object if request does not contain one.
	 * If it is null type constructor is called without arguments.
	 * Service is invoked with type name and either request object or specified request attributes values.
	 * @var string|null
	 */
	public $instanciator;

	/**
	 * Names of request attributes that should be passed to instanciator to create new object.
	 * If it is empty whole request is passed.
	 * @var string[]
	 */
	public $instanciatorArgAttrs = [];

	/**
	 * Name of the request attribute to store validation errors.
	 * If it is empty Exception\UnprocessableEntity is raised.
	 * @var string|null
	 */
	public $errorAttr = null;

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'source':
					$this->source = $value;
					break;
				case 'type':
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
		if ($this->type === null)
		{
			throw new \LogicException('Option "type" is not set');
		}
	}
}