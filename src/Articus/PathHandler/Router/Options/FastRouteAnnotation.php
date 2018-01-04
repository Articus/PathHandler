<?php
namespace Articus\PathHandler\Router\Options;

use Articus\PathHandler\Options as PHOptions;
use Zend\Stdlib\AbstractOptions;

class FastRouteAnnotation extends AbstractOptions
{
	/**
	 * Configuration for cache storage
	 * @var array|string
	 */
	protected $metadataCache = [];

	/**
	 * List of all handler class names
	 * @var string[]
	 */
	protected $handlers = [];

	/**
	 * Matched parameter name where handler name should be put
	 * @var string
	 */
	protected $handlerAttr = PHOptions::DEFAULT_HANDLER_ATTR;

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

	/**
	 * @return string[]
	 */
	public function getHandlers()
	{
		return $this->handlers;
	}

	/**
	 * @param string[] $handlers
	 * @return self
	 */
	public function setHandlers(array $handlers)
	{
		$this->handlers = $handlers;
		return $this;
	}

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
}