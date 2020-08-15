<?php
declare(strict_types=1);

namespace Articus\PathHandler\RouteInjection;

class Options
{
	/**
	 * Configuration for default router or custom router service name.
	 * After creating it will be injected with path handler routes.
	 * @var array|string
	 */
	public $router = [
		'cache' => [],
	];

	/**
	 * Map <route path prefix> -> <list of handler names that should be attached to this path prefix> .
	 * Each handler name should be available via handler plugin manager.
	 * @var array Map<string, Array<string>>
	 */
	public $paths = [];

	/**
	 * Configuration for default metadata provider or custom metadata provider service name
	 * @var array|string
	 */
	public $metadata = [
		'cache' => [],
	];

	/**
	 * Configuration for default handler plugin manager or custom handler plugin manager service name
	 * @var array|string
	 */
	public $handlers = [];

	/**
	 * Configuration for default consumer plugin manager or custom consumer plugin manager service name
	 * @var array|string
	 */
	public $consumers = [];

	/**
	 * Configuration for default attribute plugin manager or custom attribute plugin manager service name
	 * @var array|string
	 */
	public $attributes = [];

	/**
	 * Configuration for default producer plugin manager or custom producer plugin manager service name
	 * @var array|string
	 */
	public $producers = [];

	public function __construct(iterable $options)
	{
		foreach ($options as $key => $value)
		{
			switch ($key)
			{
				case 'router':
					$this->router = $value;
					break;
				case 'paths':
					$this->paths = $value;
					break;
				case 'metadata':
					$this->metadata = $value;
					break;
				case 'handlers':
					$this->handlers = $value;
					break;
				case 'consumers':
					$this->consumers = $value;
					break;
				case 'attributes':
					$this->attributes = $value;
					break;
				case 'producers':
					$this->producers = $value;
					break;
			}
		}
	}
}