<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;

/**
 * @PHA\Route(pattern="/static/test")
 * @PHA\Route(pattern="/variable/{test}")
 * @PHA\Route(pattern="/optional[/test]")
 */
class EmptyWithRoutes
{
}