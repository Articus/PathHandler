<?php
declare(strict_types=1);

namespace Articus\PathHandler;

use Generator;
use Psr\Http\Message\ServerRequestInterface;

interface MetadataProviderInterface
{
	/**
	 * Returns HTTP methods supported for handler with specified name
	 * @param string $handlerName
	 * @return string[]
	 */
	public function getHttpMethods(string $handlerName): array;

	/**
	 * Returns information about routes configured for handler with specified name
	 * @param string $handlerName
	 * @return Generator yields tuples ("route name", "route pattern", "route defaults") sorted by priority
	 */
	public function getRoutes(string $handlerName): Generator;

	/**
	 * Checks if there are any consumers configured for specified HTTP method in handler with specified name
	 * @param string $handlerName
	 * @param string $httpMethod
	 * @return bool
	 */
	public function hasConsumers(string $handlerName, string $httpMethod): bool;

	/**
	 * Returns information about consumers configured for specified HTTP method in handler with specified name
	 * @param string $handlerName
	 * @param string $httpMethod
	 * @return Generator yields tuples ("media range supported by consumer", "consumer name", "consumer options") sorted by priority
	 */
	public function getConsumers(string $handlerName, string $httpMethod): Generator;

	/**
	 * Returns information about attributes configured for specified HTTP method in handler with specified name
	 * @param string $handlerName
	 * @param string $httpMethod
	 * @return Generator yields tuples ("attribute name", "attribute options") sorted by priority
	 */
	public function getAttributes(string $handlerName, string $httpMethod): Generator;

	/**
	 * Checks if there are any producers configured for specified HTTP method in handler with specified name
	 * @param string $handlerName
	 * @param string $httpMethod
	 * @return bool
	 */
	public function hasProducers(string $handlerName, string $httpMethod): bool;

	/**
	 * Returns information about producers configured for specified HTTP method in handler with specified name
	 * @param string $handlerName
	 * @param string $httpMethod
	 * @return Generator yields tuples ("media type acceptable by producer", "producer name", "producer options") sorted by priority
	 */
	public function getProducers(string $handlerName, string $httpMethod): Generator;

	/**
	 * Executes handler method for specific HTTP method
	 * @param string $handlerName
	 * @param string $httpMethod
	 * @param object $handler
	 * @param ServerRequestInterface $request
	 * @return mixed
	 */
	public function executeHandlerMethod(string $handlerName, string $httpMethod, object $handler, ServerRequestInterface $request): mixed;
}
