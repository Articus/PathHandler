<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\IdentifiableValueLoader;
use Articus\PathHandler\Exception;
use Generator;
use Psr\Http\Message\ServerRequestInterface as Request;
use function implode;
use function sprintf;

/**
 * Simple attribute that loads list of values by their identifiers from request and stores this list as request attribute.
 * @see Options\IdentifiableValueListLoad for details.
 * @psalm-type IdentifierEmitter = callable(string, mixed...): array<int|string>
 * @psalm-type NotNull = object|resource|array|scalar
 * @psalm-type ValueReceiver = Generator<mixed, null, array{0: array-key, 1: int|string, 2: NotNull}, iterable<NotNull>>
 * @psalm-type ValueReceiverFactory = callable(string, mixed...): ValueReceiver
 */
class IdentifiableValueListLoad implements AttributeInterface
{
	/**
	 * @var IdentifierEmitter
	 */
	protected $identifierEmitter;

	/**
	 * @var ValueReceiverFactory
	 */
	protected $valueReceiverFactory;

	/**
	 * @param IdentifiableValueLoader $loader
	 * @param string $type
	 * @param IdentifierEmitter $identifierEmitter
	 * @param string[] $identifierEmitterArgAttrs
	 * @param ValueReceiverFactory $valueReceiverFactory
	 * @param string[] $valueReceiverFactoryArgAttrs
	 * @param string $valueListAttr
	 */
	public function __construct(
		protected IdentifiableValueLoader $loader,
		protected string $type,
		callable $identifierEmitter,
		/**
		 * @var string[]
		 */
		protected array $identifierEmitterArgAttrs,
		callable $valueReceiverFactory,
		/**
		 * @var string[]
		 */
		protected array $valueReceiverFactoryArgAttrs,
		protected string $valueListAttr
	)
	{
		$this->identifierEmitter = $identifierEmitter;
		$this->valueReceiverFactory = $valueReceiverFactory;
	}

	/**
	 * @inheritdoc
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
				'unknownIdentifiers' => sprintf('Unknown identifier(s): %s', implode(', ', $unknownIds))
			]);
		}

		return $request->withAttribute($this->valueListAttr, $valueReceiver->getReturn());
	}

	/**
	 * @param Request $request
	 * @return array<int|string>
	 */
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

	/**
	 * @param Request $request
	 * @return ValueReceiver
	 */
	protected function getValueReceiver(Request $request): Generator
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
