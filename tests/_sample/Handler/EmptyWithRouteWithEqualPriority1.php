<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;

/**
 * @PHA\Route(pattern="/priority/{test:\d+}", priority=5)
 */
class EmptyWithRouteWithEqualPriority1
{
}