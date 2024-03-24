<?php
declare(strict_types=1);

namespace Articus\PathHandler\Attribute;

use Articus\DataTransfer\Service as DTService;
use Articus\DataTransfer\Strategy\ExtractorInterface;
use Articus\DataTransfer\Strategy\StrategyInterface;
use Articus\DataTransfer\Validator\ValidatorInterface;
use Articus\PathHandler\Exception;
use Articus\PathHandler\Middleware;
use LogicException;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Simple attribute that transfer data from specified source using specified strategy and validator
 * View Options\Transfer for details.
 */
class AnonymousTransfer implements AttributeInterface
{
	protected ExtractorInterface $noopExtractor;

	public function __construct(
		protected DTService $dtService,
		protected string $source,
		protected StrategyInterface $strategy,
		protected ValidatorInterface $validator,
		protected string $objectAttr,
		protected null|string $errorAttr
	)
	{
		$this->noopExtractor = new class () implements ExtractorInterface
		{
			public function extract($from)
			{
				return $from;
			}
		};
	}

	/**
	 * @inheritdoc
	 * @throws Exception\UnprocessableEntity
	 */
	public function __invoke(Request $request): Request
	{
		$data = $this->getData($request);
		$object = $request->getAttribute($this->objectAttr);
		$error = $this->dtService->transfer($data, $this->noopExtractor, $object, $this->strategy, $this->strategy, $this->validator, $this->strategy);
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
			Transfer::SOURCE_GET => $request->getQueryParams(),
			Transfer::SOURCE_POST => $request->getAttribute(Middleware::PARSED_BODY_ATTR_NAME),
			Transfer::SOURCE_ROUTE => $this->getRouteData($request),
			Transfer::SOURCE_HEADER => $request->getHeaders(),
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
}
