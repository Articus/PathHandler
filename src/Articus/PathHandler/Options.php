<?php

namespace Articus\PathHandler;

use Zend\Expressive\Router\RouterInterface;
use Zend\Stdlib\AbstractOptions;

class Options extends AbstractOptions
{
	const DEFAULT_HANDLER_ATTR = 'handler';
	/**
	 * Request attribute that contains handler name
	 * @var string
	 */
	protected $handlerAttr = self::DEFAULT_HANDLER_ATTR;
	/**
	 * Configuration for router
	 * @var array|string
	 */
	protected $routes = RouterInterface::class;
	/**
	 * Configuration for handler plugin manager
	 * @var array|string
	 */
	protected $handlers = [];
	/**
	 * Configuration for consumer plugin manager
	 * @var array|string
	 */
	protected $consumers = [];
	/**
	 * Configuration for attribute plugin manager
	 * @var array|string
	 */
	protected $attributes = [];
	/**
	 * Configuration for producer plugin manager
	 * @var array|string
	 */
	protected $producers = [];
	/**
	 * Configuration for cache storage
	 * @var array|string
	 */
	protected $metadataCache = [];

	/**
	 * @return string
	 */
	public function getHandlerAttr()
	{
		return $this->handlerAttr;
	}

	/**
	 * @param string $handlerAttr
	 * @return self
	 */
	public function setHandlerAttr($handlerAttr)
	{
		$this->handlerAttr = $handlerAttr;
		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * @param array|string $routes
	 * @return self
	 */
	public function setRoutes($routes)
	{
		$this->routes = $routes;
		return $this;
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
	 * @return self
	 */
	public function setHandlers($handlers)
	{
		$this->handlers = $handlers;
		return $this;
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
	 * @return self
	 */
	public function setConsumers($consumers)
	{
		$this->consumers = $consumers;
		return $this;
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
	 * @return self
	 */
	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;
		return $this;
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
	 * @return self
	 */
	public function setProducers($producers)
	{
		$this->producers = $producers;
		return $this;
	}

	/**
	 * @return array|string
	 */
	public function getMetadataCache()
	{
		return $this->metadataCache;
	}

	/**
	 * @param array|string $metadataCache
	 * @return self
	 */
	public function setMetadataCache($metadataCache)
	{
		$this->metadataCache = $metadataCache;
		return $this;
	}

}