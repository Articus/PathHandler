<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler\Exception;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Simple attribute that transfer data from specified source to newly created or existing object.
 * View Options\Transfer for details.
 */
class Transfer implements AttributeInterface
{
	const SOURCE_GET = 'get';
	const SOURCE_POST = 'post';
	const SOURCE_ROUTE = 'route';
	const SOURCE_HEADER = 'header';
	const SOURCE_ATTRIBUTE = 'attribute';

	/**
	 * @var DTService
	 */
	protected $dtService;

	/**
	 * @var string
	 */
	protected $source;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $subset;

	/**
	 * @var string
	 */
	protected $objectAttr;

	/**
	 * @var callable
	 */
	protected $instanciator;

	/**
	 * @var string[]
	 */
	protected $instanciatorArgAttrs;

	/**
	 * @var string|null
	 */
	protected $errorAttr;

	/**
	 * @param DTService $dtService
	 * @param string $source
	 * @param string $type
	 * @param string $subset
	 * @param string $objectAttr
	 * @param callable $instanciator
	 * @param string[] $instanciatorArgAttrs
	 * @param null|string $errorAttr
	 */
	public function __construct(
		DTService $dtService,
		string $source,
		string $type,
		string $subset,
		string $objectAttr,
		callable $instanciator,
		array $instanciatorArgAttrs,
		?string $errorAttr
	)
	{
		$this->dtService = $dtService;
		$this->source = $source;
		$this->type = $type;
		$this->subset = $subset;
		$this->objectAttr = $objectAttr;
		$this->instanciator = $instanciator;
		$this->instanciatorArgAttrs = $instanciatorArgAttrs;
		$this->errorAttr = $errorAttr;
	}

	/**
	 * @param Request $request
	 * @return Request
	 * @throws Exception\BadRequest
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

	/**
	 * @param Request $request
	 * @return mixed
	 */
	protected function getData(Request $request)
	{
		$data = null;
		switch ($this->source)
		{
			case self::SOURCE_GET:
				$data = $request->getQueryParams();
				break;
			case self::SOURCE_POST:
				$data = $request->getParsedBody();
				break;
			case self::SOURCE_ROUTE:
				$routeResult = $request->getAttribute(RouteResult::class);
				if (!($routeResult instanceof RouteResult))
				{
					throw new \LogicException('Failed to find routing result.');
				}
				$data = $routeResult->getMatchedParams();
				break;
			case self::SOURCE_HEADER:
				$data = [];
				foreach ($request->getHeaders() as $name => $values)
				{
					$data[$name] = (\count($values) === 1) ? $values[0] : $values;
				}
				break;
			case self::SOURCE_ATTRIBUTE:
				$data = $request->getAttributes();
				break;
			default:
				throw new \InvalidArgumentException(\sprintf('Unknown source %s.', $this->source));
		}
		return $data;
	}

	/**
	 * @param Request $request
	 * @return object
	 */
	protected function getObject(Request $request)
	{
		if (!\class_exists($this->type))
		{
			throw new \InvalidArgumentException(\sprintf('Unknown class %s.', $this->type));
		}

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