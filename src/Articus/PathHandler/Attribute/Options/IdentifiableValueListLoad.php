<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute\Options;

class IdentifiableValueListLoad
{
	/**
	 * Type of identifiable values registered in Articus\DataTransfer\IdentifiableValueLoader service
	 * @var string
	 */
	public $type;

	/**
	 * Name of the service that should be used to emit identifiers of list values.
	 * Service is invoked with type name and either request object or specified request attributes values,
	 * so callable(string, mixed...): array<int|string> is expected.
	 * @var string
	 */
	public $identifierEmitter;

	/**
	 * Names of request attributes that should be passed to identifier emitter.
	 * If it is empty whole request is passed.
	 * @var string[]
	 */
	public $identifierEmitterArgAttrs = [];

	/**
	 * Name of the service that should be used to instanciate "value receiver".
	 * Service is invoked with type name and either request object or specified request attributes values,
	 * so callable(string, mixed...): Generator<int, null, null|array{0:int|string,1:int|string,2:object|resource|array|string|int|float|bool},iterable<int|string, object|resource|array|string|int|float|bool>> is expected.
	 * The generator instanciated with this service ("value receiver") is expected
	 * to receive tuples ("identifier index", "identifier", "value corresponding to identifier") until null is sent
	 * and to return value list that should be stored in request.
	 * @var string|null
	 */
	public $valueReceiverFactory = null;

	/**
	 * Names of request attributes that should be passed to value receiver factory.
	 * If it is empty whole request is passed.
	 * @var string[]
	 */
	public $valueReceiverFactoryArgAttrs = [];

	/**
	 * Name of the request attribute to store value list
	 * @var string
	 */
	public $valueListAttr = 'list';

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'type':
					$this->type = $value;
					break;
				case 'identifierEmitter':
				case 'identifier_emitter':
				case 'idEmitter':
				case 'id_emitter':
					$this->identifierEmitter = $value;
					break;
				case 'identifierEmitterArgAttrs':
				case 'identifier_emitter_arg_attrs':
				case 'idEmitterArgAttrs':
				case 'id_emitter_arg_attrs':
					$this->identifierEmitterArgAttrs = $value;
					break;
				case 'valueReceiverFactory':
				case 'value_receiver_factory':
					$this->valueReceiverFactory = $value;
					break;
				case 'valueReceiverFactoryArgAttrs':
				case 'value_receiver_factory_arg_attrs':
					$this->valueReceiverFactoryArgAttrs = $value;
					break;
				case 'valueListAttr':
				case 'value_list_attr':
				case 'listAttr':
				case 'list_attr':
					$this->valueListAttr = $value;
					break;
			}
		}
		switch (true)
		{
			case ($this->type === null):
				throw new \LogicException('Option "type" is not set');
			case ($this->identifierEmitter === null):
				throw new \LogicException('Option "identifierEmitter" is not set');
		}
	}
}
