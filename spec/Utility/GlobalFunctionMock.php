<?php
declare(strict_types=1);

namespace spec\Utility;

use Mockery\Expectation;
use Mockery\ExpectationInterface;
use Mockery\HigherOrderMessage;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use function array_keys;
use function extension_loaded;
use function mock;
use function uopz_set_return;
use function uopz_unset_return;

class GlobalFunctionMock
{
	protected static null|MockInterface|LegacyMockInterface $innerMock;
	/**
	 * @var array<string, true>
	 */
	protected static array $functionNameMap = [];

	public static function shouldReceive(string $functionName): Expectation|ExpectationInterface|HigherOrderMessage
	{
		if (!isset(self::$innerMock))
		{
			self::$innerMock = mock();
		}
		if (!isset(self::$functionNameMap[$functionName]))
		{
			$mock = self::$innerMock;
			uopz_set_return(
				$functionName,
				static fn(...$arguments) => $mock->{$functionName}(...$arguments),
				true
			);
			self::$functionNameMap[$functionName] = true;
		}
		return self::$innerMock->shouldReceive($functionName);
	}

	public static function tearDown(): void
	{
		foreach (array_keys(self::$functionNameMap) as $functionName)
		{
			uopz_unset_return($functionName);
			unset(self::$functionNameMap[$functionName]);
		}
		self::$innerMock = null;
	}

	public static function disabled(): bool
	{
		return (!extension_loaded('uopz'));
	}
}
