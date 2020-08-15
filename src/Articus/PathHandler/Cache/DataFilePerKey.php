<?php
declare(strict_types=1);

namespace Articus\PathHandler\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Incomplete implementation of PSR-16 optimized to store single array of non-object data.
 * Data for key "my_key" is stored as plain PHP array in file <directory>/my_key.php
 */
class DataFilePerKey implements CacheInterface
{
	/**
	 * Key to access data
	 * @var string
	 */
	protected $key;
	/**
	 * Root folder to store data
	 * @var string|null
	 */
	protected $directory;
	/**
	 * Permissions that should be removed from file used to store data.
	 * Similar to https://www.php.net/manual/en/function.umask.php
	 * @var int
	 */
	protected $umask;
	/**
	 * @var \Closure
	 */
	protected static $emptyErrorHandler;

	/**
	 * @param string|null $directory
	 * @param int $umask
	 */
	public function __construct(string $key, ?string $directory, int $umask = 0002)
	{
		$this->key = $key;
		$this->umask = $umask;
		if ($directory !== null)
		{
			if (!(\is_dir($directory) || @\mkdir($directory, 0777 & (~$this->umask), true)))
			{
				throw new \InvalidArgumentException(\sprintf('The directory "%s" does not exist and could not be created.', $directory));
			}
			if (!\is_writable($directory))
			{
				throw new \InvalidArgumentException(\sprintf('The directory "%s" is not writable.', $directory));
			}
			$this->directory = \realpath($directory);
		}
		self::$emptyErrorHandler = static function () {};
	}

	/**
	 * @inheritDoc
	 */
	public function get($key, $default = null)
	{
		$result = $default;
		if (($this->directory !== null) && ($this->key === $key))
		{
			$filename = $this->getFilename();
			// note: error suppression is still faster than `file_exists`, `is_file` and `is_readable`
			\set_error_handler(self::$emptyErrorHandler);
			$value = include $filename;
			\restore_error_handler();
			if (\is_array($value))
			{
				$result = $value;
			}
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function set($key, $value, $ttl = null)
	{
		$result = false;
		if (($this->directory !== null) && ($this->key === $key) && \is_array($value) && ($ttl === null))
		{
			$filename = $this->getFilename();
			$content = \sprintf('<?php return %s;', \var_export($value, true));
			$result = $this->writeFile($filename, $content);
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function delete($key)
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function clear()
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function getMultiple($keys, $default = null)
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function setMultiple($values, $ttl = null)
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function deleteMultiple($keys)
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @inheritDoc
	 */
	public function has($key)
	{
		throw new \LogicException('Not implemented');
	}

	protected function getFilename(): string
	{
		return $this->directory . \DIRECTORY_SEPARATOR . $this->key . '.php';
	}

	protected function writeFile(string $filename, string $content): bool
	{
		$result = false;
		$temporaryFilename = \tempnam($this->directory, 'swap');
		if (\file_put_contents($temporaryFilename, $content) !== false)
		{
			@\chmod($temporaryFilename, 0666 & (~$this->umask));
			if (@\rename($temporaryFilename, $filename))
			{
				$result = true;
			}
			else
			{
				@\unlink($temporaryFilename);
			}
		}
		return $result;
	}
}