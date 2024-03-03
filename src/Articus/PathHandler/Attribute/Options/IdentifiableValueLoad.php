<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Options;

class IdentifiableValueLoad
{
	/**
	 * Type of identifiable values registered in \Articus\DataTransfer\IdentifiableValueLoader service
	 * @see \Articus\DataTransfer\IdentifiableValueLoader
	 */
	public string $type;

	/**
	 * Name of the request attribute that contains value identifier
	 */
	public string $identifierAttr = 'id';

	/**
	 * Name of the request attribute to store loaded identifiable value
	 */
	public string $valueAttr = 'value';

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'type':
					$this->type = $value;
					break;
				case 'identifierAttr':
				case 'identifier_attr':
				case 'idAttr':
				case 'id_attr':
					$this->identifierAttr = $value;
					break;
				case 'valueAttr':
				case 'value_attr':
					$this->valueAttr = $value;
					break;
			}
		}
	}
}
