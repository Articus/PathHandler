<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler\Exception;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Simple attribute that loads value by identifier from specified request attribute and stores this value as request attribute.
 * View Options\IdentifiableValueLoad for details.
 */
class IdentifiableValueLoad implements AttributeInterface
{
	/**
	 * @var IdentifiableValueLoader
	 */
	protected $loader;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $identifierAttr;

	/**
	 * @var string
	 */
	protected $valueAttr;

	/**
	 * @param IdentifiableValueLoader $loader
	 * @param string $type
	 * @param string $identifierAttr
	 * @param string $valueAttr
	 */
	public function __construct(IdentifiableValueLoader $loader, string $type, string $identifierAttr, string $valueAttr)
	{
		$this->loader = $loader;
		$this->type = $type;
		$this->identifierAttr = $identifierAttr;
		$this->valueAttr = $valueAttr;
	}

	/**
	 * @param Request $request
	 * @return Request
	 * @throws Exception\NotFound
	 */
	public function __invoke(Request $request): Request
	{
		$id = $request->getAttribute($this->identifierAttr);
		if (!(\is_int($id) || \is_string($id)))
		{
			throw new \LogicException('Failed to find valid identifier.');
		}
		$value = $this->loader->get($this->type, $id);
		if ($value === null)
		{
			throw new Exception\NotFound();
		}
		return $request->withAttribute($this->valueAttr, $value);
	}
}
