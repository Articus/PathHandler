<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler\Exception;
use LogicException;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface as Request;
use function count;

/**
 * Simple attribute that transfer data from specified source to newly created or existing object.
 * View Options\Transfer for details.
 * @psalm-type Instanciator = callable(class-string, mixed...): object
 */
class Transfer implements AttributeInterface
{
	public const SOURCE_GET = 'get';
	public const SOURCE_POST = 'post';
	public const SOURCE_ROUTE = 'route';
	public const SOURCE_HEADER = 'header';
	public const SOURCE_ATTRIBUTE = 'attribute';

	/**
	 * @var Instanciator
	 */
	protected $instanciator;

	/**
	 * @param DTService $dtService
	 * @param string $source
	 * @param class-string $type
	 * @param string $subset
	 * @param string $objectAttr
	 * @param Instanciator $instanciator
	 * @param string[] $instanciatorArgAttrs
	 * @param null|string $errorAttr
	 */
	public function __construct(
		protected DTService $dtService,
		protected string $source,
		/**
		 * @var class-string
		 */
		protected string $type,
		protected string $subset,
		protected string $objectAttr,
		callable $instanciator,
		/**
		 * @var string[]
		 */
		protected array $instanciatorArgAttrs,
		protected null|string $errorAttr
	)
	{
		$this->instanciator = $instanciator;
	}

	/**
	 * @inheritdoc
	 * @throws Exception\UnprocessableEntity
	 */
	public function __invoke(Request $request): Request
	{
		$data = $this->getData($request);
		$object = $this->getObject($request);
		$error = $this->dtService->transferToTypedData($data, $object, $this->subset);
		if (empty($error))
		{
			$request = $request->withAttribute($this->objectAttr, $object);
		}
		elseif (empty($this->errorAttr))
		{
			throw new Exception\UnprocessableEntity($error);
		}
		else
		{
			$request = $request->withAttribute($this->errorAttr, $error);
		}

		return $request;
	}

	protected function getData(Request $request): mixed
	{
		return match($this->source)
		{
			self::SOURCE_GET => $request->getQueryParams(),
			self::SOURCE_POST => $request->getParsedBody(),
			self::SOURCE_ROUTE => $this->getRouteData($request),
			self::SOURCE_HEADER => $this->getHeaderData($request),
			self::SOURCE_ATTRIBUTE => $request->getAttributes(),
		};
	}

	protected function getRouteData(Request $request): array
	{
		$routeResult = $request->getAttribute(RouteResult::class);
		if (!($routeResult instanceof RouteResult))
		{
			throw new LogicException('Failed to find routing result.');
		}
		return $routeResult->getMatchedParams();
	}

	protected function getHeaderData(Request $request): array
	{
		$data = [];
		foreach ($request->getHeaders() as $name => $values)
		{
			$data[$name] = (count($values) === 1) ? $values[0] : $values;
		}
		return $data;
	}

	protected function getObject(Request $request): object
	{
		$result = $request->getAttribute($this->objectAttr);
		if (!($result instanceof $this->type))
		{
			$instanciatorArgs = [$request];
			if (!empty($this->instanciatorArgAttrs))
			{
				$instanciatorArgs = [];
				foreach ($this->instanciatorArgAttrs as $instanciatorArgAttr)
				{
					$instanciatorArgs[] = $request->getAttribute($instanciatorArgAttr);
				}
			}
			$result = ($this->instanciator)($this->type, ...$instanciatorArgs);
		}
		return $result;
	}
}
