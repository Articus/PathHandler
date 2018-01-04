<?php
namespace Test\PathHandler\Sample\Handler;

use Articus\PathHandler\Annotation as PHA;

/**
 * @PHA\Route(pattern="/optionals[/test1[/test2]]")
 */
class EmptyWithOptionalsRoute
{
}