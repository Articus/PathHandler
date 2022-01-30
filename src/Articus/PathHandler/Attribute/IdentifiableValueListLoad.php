<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler\Exception;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Simple attribute that loads list of values by their identifiers from request and stores this list as request attribute.
 * View Options\IdentifiableValueListLoad for details.
 */
class IdentifiableValueListLoad implements AttributeInterface
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
	 * @var callable(string, mixed...): array<int|string>
	 */
	protected $identifierEmitter;

	/**
	 * @var string[]
	 */
	protected $identifierEmitterArgAttrs;

	/**
	 * @var callable(string, mixed...): \Generator
	 */
	protected $valueReceiverFactory;

	/**
	 * @var string[]
	 */
	protected $valueReceiverFactoryArgAttrs;

	/**
	 * @var string
	 */
	protected $valueListAttr;

	/**
	 * @param IdentifiableValueLoader $loader
	 * @param string $type
	 * @param callable $identifierEmitter
	 * @param string[] $identifierEmitterArgAttrs
	 * @param callable $valueReceiverFactory
	 * @param string[] $valueReceiverFactoryArgAttrs
	 * @param string $valueListAttr
	 */
	public function __construct(
		IdentifiableValueLoader $loader,
		string $type,
		callable $identifierEmitter,
		array $identifierEmitterArgAttrs,
		callable $valueReceiverFactory,
		array $valueReceiverFactoryArgAttrs,
		string $valueListAttr
	)
	{
		$this->loader = $loader;
		$this->type = $type;
		$this->identifierEmitter = $identifierEmitter;
		$this->identifierEmitterArgAttrs = $identifierEmitterArgAttrs;
		$this->valueReceiverFactory = $valueReceiverFactory;
		$this->valueReceiverFactoryArgAttrs = $valueReceiverFactoryArgAttrs;
		$this->valueListAttr = $valueListAttr;
	}

	/**
	 * @param Request $request
	 * @return Request
	 * @throws Exception\UnprocessableEntity
	 */
	public function __invoke(Request $request): Request
	{
		$ids = $this->getIdentifiers($request);
		$unknownIds = [];
		$valueReceiver = $this->getValueReceiver($request);

		$this->loader->wishMultiple($this->type, $ids);
		foreach ($ids as $idIndex => $id)
		{
			$value = $this->loader->get($this->type, $id);
			if ($value === null)
			{
				$unknownIds[$idIndex] = $id;
			}
			else
			{
				$valueReceiver->send([$idIndex, $id, $value]);
			}
		}
		$valueReceiver->send(null);

		if (!empty($unknownIds))
		{
			throw new Exception\UnprocessableEntity([
				'unknownIdentifiers' => \sprintf('Unknown identifier(s): %s', \implode(', ', $unknownIds))
			]);
		}

		return $request->withAttribute($this->valueListAttr, $valueReceiver->getReturn());
	}

	protected function getIdentifiers(Request $request): array
	{
		$emitterArgs = [$request];
		if (!empty($this->identifierEmitterArgAttrs))
		{
			$emitterArgs = [];
			foreach ($this->identifierEmitterArgAttrs as $emitterArgAttr)
			{
				$emitterArgs[] = $request->getAttribute($emitterArgAttr);
			}
		}
		return ($this->identifierEmitter)($this->type, ...$emitterArgs);
	}

	protected function getValueReceiver(Request $request): \Generator
	{
		$receiverArgs = [$request];
		if (!empty($this->valueReceiverFactoryArgAttrs))
		{
			$receiverArgs = [];
			foreach ($this->valueReceiverFactoryArgAttrs as $receiverArgAttr)
			{
				$receiverArgs[] = $request->getAttribute($receiverArgAttr);
			}
		}
		return ($this->valueReceiverFactory)($this->type, ...$receiverArgs);
	}
}
