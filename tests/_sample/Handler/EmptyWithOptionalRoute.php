<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;

/**
 * @PHA\Route(pattern="/optional[/test]")
 */
class EmptyWithOptionalRoute
{
}