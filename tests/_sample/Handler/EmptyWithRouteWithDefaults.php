<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;

/**
 * @PHA\Route(pattern="/static/test", defaults={"test1": 123, "test2": "qwer"})
 */
class EmptyWithRouteWithDefaults
{
}