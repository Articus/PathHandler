<?php
namespace Test\PathHandler\Sample\Exception;

use Articus\PathHandler\Exception\HeaderInterface;
use Articus\PathHandler\Exception\HttpCode;

class WithHeader extends HttpCode implements HeaderInterface
{
	/**
	 * @inheritdoc
	 */
	public function getHeaders()
	{
		yield 'x-test' => 'header';
	}
}