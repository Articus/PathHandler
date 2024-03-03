<?php
declare(strict_types=1);

namespace Articus\PathHandler\Cache;

use Closure;
use DateInterval;
use InvalidArgumentException;
use LogicException;
use Psr\SimpleCache\CacheInterface;
use function chmod;
use function file_put_contents;
use function is_array;
use function is_dir;
use function is_writable;
use function mkdir;
use function realpath;
use function rename;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function tempnam;
use function unlink;
use function var_export;
use const DIRECTORY_SEPARATOR;

/**
 * Incomplete implementation of PSR-16 optimized to store single array of non-object data.
 * Data for key "my_key" is stored as plain PHP array in file "directory"/my_key.php
 */
class DataFilePerKey implements CacheInterface
{
	/**
	 * Key to access data
	 */
	protected string $key;

	/**
	 * Root folder to store data
	 */
	protected null|string $directory = null;

	/**
	 * Permissions that should be removed from file used to store data.
	 * Similar to https://www.php.net/manual/en/function.umask.php
	 */
	protected int $umask;

	protected static Closure $emptyErrorHandler;

	public function __construct(string $key, null|string $directory, int $umask = 0002)
	{
		$this->key = $key;
		$this->umask = $umask;
		if ($directory !== null)
		{
			if (!(is_dir($directory) || @mkdir($directory, 0777 & (~$this->umask), true)))
			{
				throw new InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $directory));
			}
			if (!is_writable($directory))
			{
				throw new InvalidArgumentException(sprintf('The directory "%s" is not writable.', $directory));
			}
			$this->directory = realpath($directory);
		}
		self::$emptyErrorHandler = static function () {};
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		$result = $default;
		if (($this->directory !== null) && ($this->key === $key))
		{
			$filename = $this->getFilename();
			// note: error suppression is still faster than `file_exists`, `is_file` and `is_readable`
			set_error_handler(self::$emptyErrorHandler);
			$value = include $filename;
			restore_error_handler();
			if (is_array($value))
			{
				$result = $value;
			}
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
	{
		$result = false;
		if (($this->directory !== null) && ($this->key === $key) && is_array($value) && ($ttl === null))
		{
			$filename = $this->getFilename();
			$content = sprintf('<?php return %s;', var_export($value, true));
			$result = $this->writeFile($filename, $content);
		}
		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function delete(string $key): bool
	{
		throw new LogicException('Not implemented');
	}

	/**
	 * @inheritdoc
	 */
	public function clear(): bool
	{
		throw new LogicException('Not implemented');
	}

	/**
	 * @inheritdoc
	 */
	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		throw new LogicException('Not implemented');
	}

	/**
	 * @inheritdoc
	 */
	public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
	{
		throw new LogicException('Not implemented');
	}

	/**
	 * @inheritdoc
	 */
	public function deleteMultiple(iterable $keys): bool
	{
		throw new LogicException('Not implemented');
	}

	/**
	 * @inheritdoc
	 */
	public function has(string $key): bool
	{
		throw new LogicException('Not implemented');
	}

	protected function getFilename(): string
	{
		return $this->directory . DIRECTORY_SEPARATOR . $this->key . '.php';
	}

	protected function writeFile(string $filename, string $content): bool
	{
		$result = false;
		$temporaryFilename = tempnam($this->directory, 'swap');
		if (file_put_contents($temporaryFilename, $content) !== false)
		{
			@chmod($temporaryFilename, 0666 & (~$this->umask));
			if (@rename($temporaryFilename, $filename))
			{
				$result = true;
			}
			else
			{
				@unlink($temporaryFilename);
			}
		}
		return $result;
	}
}
