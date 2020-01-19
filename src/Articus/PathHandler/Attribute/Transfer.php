<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\Service as DTService;
use Articus\PathHandler\Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Router\RouteResult;

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
	 * @var Options\Transfer
	 */
	protected $options;

	/**
	 * @param DTService $dtService
	 * @param Options\Transfer $options
	 */
	public function __construct(DTService $dtService, Options\Transfer $options)
	{
		$this->dtService = $dtService;
		$this->options = $options;
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
		$error = $this->dtService->transferToTypedData($data, $object, $this->options->getSubset());
		if (empty($error))
		{
			$request = $request->withAttribute($this->options->getObjectAttr(), $object);
		}
		elseif (empty($this->options->getErrorAttr()))
		{
			throw new Exception\UnprocessableEntity($error);
		}
		else
		{
			$request = $request->withAttribute($this->options->getErrorAttr(), $error);
		}

		return $request;
	}

	/**
	 * @param Request $request
	 * @return array
	 * @throws Exception\BadRequest
	 */
	protected function getData(Request $request): array
	{
		$data = null;
		switch ($this->options->getSource())
		{
			case self::SOURCE_GET:
				$data = $request->getQueryParams();
				break;
			case self::SOURCE_POST:
				$data = $request->getParsedBody();
				if (!\is_array($data))
				{
					throw new Exception\BadRequest('Unexpected content');
				}
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
				throw new \InvalidArgumentException(\sprintf('Unknown source %s.', $this->options->getSource()));
		}
		return $data;
	}

	/**
	 * @param Request $request
	 * @return object
	 */
	protected function getObject(Request $request)
	{
		$className = $this->options->getType();
		if (!\class_exists($className))
		{
			throw new \InvalidArgumentException(\sprintf('Unknown class %s.', $this->options->getType()));
		}

		$result = $request->getAttribute($this->options->getObjectAttr());
		if (!($result instanceof $className))
		{
			//TODO use Doctrine instantiator instead?
			$result = new $className();
		}
		return $result;
	}
}