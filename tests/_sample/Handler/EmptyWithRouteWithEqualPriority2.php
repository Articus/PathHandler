<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;

/**
 * @PHA\Route(pattern="/priority/{test}", priority=5)
 */
class EmptyWithRouteWithEqualPriority2
{
}