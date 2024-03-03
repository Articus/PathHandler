<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler\Exception;
use LogicException;
use Psr\Http\Message\ServerRequestInterface as Request;
use function is_int;
use function is_string;

/**
 * Simple attribute that loads value by identifier from specified request attribute and stores this value as request attribute.
 * View Options\IdentifiableValueLoad for details.
 */
class IdentifiableValueLoad implements AttributeInterface
{
	public function __construct(
		protected IdentifiableValueLoader $loader,
		protected string $type,
		protected string $identifierAttr,
		protected string $valueAttr
	)
	{
	}

	/**
	 * @inheritdoc
	 * @throws Exception\NotFound
	 */
	public function __invoke(Request $request): Request
	{
		$id = $request->getAttribute($this->identifierAttr);
		if (!(is_int($id) || is_string($id)))
		{
			throw new LogicException('Failed to find valid identifier.');
		}
		$value = $this->loader->get($this->type, $id);
		if ($value === null)
		{
			throw new Exception\NotFound();
		}
		return $request->withAttribute($this->valueAttr, $value);
	}
}
