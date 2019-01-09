<?php
declare(strict_types=1);

namespace Articus\PathHandler\RouteInjection;

use Zend\Stdlib\AbstractOptions;

class Options extends AbstractOptions
{
	/**
	 * Configuration for default router or custom router service name.
	 * After creating it will be injected with path handler routes.
	 * @var array|string
	 */
	protected $router = [
		'cache' => [
			'adapter' => 'blackhole',
		],
	];

	/**
	 * Map <route path prefix> -> <list of handler names that should be attached to this path prefix> .
	 * Each handler name should be available via handler plugin manager.
	 * @var array Map<string, Array<string>>
	 */
	protected $paths = [];

	/**
	 * Configuration for default metadata provider or custom metadata provider service name
	 * @var array|string
	 */
	protected $metadata = [
		'cache' => [
			'adapter' => 'blackhole',
		],
	];

	/**
	 * Configuration for default handler plugin manager or custom handler plugin manager service name
	 * @var array|string
	 */
	protected $handlers = [];

	/**
	 * Configuration for default consumer plugin manager or custom consumer plugin manager service name
	 * @var array|string
	 */
	protected $consumers = [];

	/**
	 * Configuration for default attribute plugin manager or custom attribute plugin manager service name
	 * @var array|string
	 */
	protected $attributes = [];

	/**
	 * Configuration for default producer plugin manager or custom producer plugin manager service name
	 * @var array|string
	 */
	protected $producers = [];

	/**
	 * @return array|string
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * @param array|string $router
	 */
	public function setRouter($router): void
	{
		$this->router = $router;
	}

	/**
	 * @return array
	 */
	public function getPaths(): array
	{
		return $this->paths;
	}

	/**
	 * @param array $paths
	 */
	public function setPaths(array $paths): void
	{
		$this->paths = $paths;
	}

	/**
	 * @return array|string
	 */
	public function getMetadata()
	{
		return $this->metadata;
	}

	/**
	 * @param array|string $metadata
	 */
	public function setMetadata($metadata): void
	{
		$this->metadata = $metadata;
	}

	/**
	 * @return array|string
	 */
	public function getHandlers()
	{
		return $this->handlers;
	}

	/**
	 * @param array|string $handlers
	 */
	public function setHandlers($handlers): void
	{
		$this->handlers = $handlers;
	}

	/**
	 * @return array|string
	 */
	public function getConsumers()
	{
		return $this->consumers;
	}

	/**
	 * @param array|string $consumers
	 */
	public function setConsumers($consumers): void
	{
		$this->consumers = $consumers;
	}

	/**
	 * @return array|string
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * @param array|string $attributes
	 */
	public function setAttributes($attributes): void
	{
		$this->attributes = $attributes;
	}

	/**
	 * @return array|string
	 */
	public function getProducers()
	{
		return $this->producers;
	}

	/**
	 * @param array|string $producers
	 */
	public function setProducers($producers): void
	{
		$this->producers = $producers;
	}
}