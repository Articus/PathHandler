<?php

namespace Articus\PathHandler\Router;


use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Router\Http\TreeRouteStack;
use Zend\Psr7Bridge\Psr7ServerRequest;

/**
 * Simplified replacement of Zend\Expressive\Router\ZendRouter,
 * which allows to create Zend\Expressive\Router\RouterInterface from pre-populated Zend\Router\Http\TreeRouteStack .
 * Zend\Expressive\Router\ZendRouter does not support that since 1.3 .
 * See https://github.com/zendframework/zend-expressive-zendrouter/issues/18 for more details.
 */
class TreeConfiguration implements RouterInterface
{
	/**
	 * @var TreeRouteStack
	 */
	protected $treeRouter;

	public function __construct(TreeRouteStack $treeRouter)
	{
		$this->treeRouter = $treeRouter;
	}

	/**
	 * @inheritdoc
	 */
	public function addRoute(Route $route)
	{
		throw new \LogicException(sprintf(
			'%s does not support dynamic route adding. Please, supply all routes in constructor.',
			self::class
		));
	}

	/**
	 * @inheritdoc
	 * @return RouteResult
	 */
	public function match(Request $request)
	{
		$match = $this->treeRouter->match(Psr7ServerRequest::toZend($request, true));

		$result = null;
		if (null === $match)
		{
			$result = RouteResult::fromRouteFailure();
		}
		else
		{
			$result = RouteResult::fromRoute(
				new Route(
					$match->getMatchedRouteName(),
					'',
					Route::HTTP_METHOD_ANY,
					$match->getMatchedRouteName()
				),
				$match->getParams()
			);
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function generateUri($name, array $substitutions = [], array $options = [])
	{
		$options = array_merge($options, [
			'name' => $name,
			'only_return_path' => true,
		]);

		return $this->treeRouter->assemble($substitutions, $options);
	}
}