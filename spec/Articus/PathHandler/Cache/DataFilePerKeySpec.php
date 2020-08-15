<?php
declare(strict_types=1);

namespace spec\Articus\PathHandler\Cache;

use Articus\PathHandler as PH;
use PhpSpec\ObjectBehavior;
use spec\Utility\GlobalFunctionMock;

class DataFilePerKeySpec extends ObjectBehavior
{
	protected const TEST_CACHE_FOLDER = 'data/cache';
	protected const TEST_CACHE_KEY = 'test_key';
	protected const TEST_CACHE_FILE = self::TEST_CACHE_FOLDER . '/' . self::TEST_CACHE_KEY . '.php';
	protected const TEST_CACHE_DATA = ['test' => 123];
	protected const TEST_CACHE_CONTENT = <<<'CACHE_CONTENT'
<?php return array (
  'test' => 123,
);
CACHE_CONTENT;

	public function letGo()
	{
		GlobalFunctionMock::tearDown();
		//Have to place here - "let" invokes constructor :(
		$folder = self::TEST_CACHE_FOLDER;
		if (\is_dir($folder))
		{
			$files = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ($files as $name => $file)
			{
				if ($file->isDir())
				{
					\rmdir($name);
				}
				else
				{
					\unlink($name);
				}
			}
			\rmdir($folder);
		}
	}

	public function it_throws_if_cache_folder_does_not_exists_and_can_not_be_created()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$exception = new \InvalidArgumentException(\sprintf('The directory "%s" does not exist and could not be created.', $folder));

		GlobalFunctionMock::shouldReceive('is_dir')->with($folder)->andReturn(false);
		GlobalFunctionMock::shouldReceive('mkdir')->with($folder, 0775, true)->andReturn(false);
		$this->beConstructedWith($key, $folder);
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_throws_if_cache_folder_is_not_writable()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$exception = new \InvalidArgumentException(\sprintf('The directory "%s" is not writable.', $folder));

		GlobalFunctionMock::shouldReceive('is_dir')->with($folder)->andReturn(false);
		GlobalFunctionMock::shouldReceive('mkdir')->with($folder, 0775, true)->andReturn(true);
		GlobalFunctionMock::shouldReceive('is_writable')->with($folder)->andReturn(false);
		$this->beConstructedWith($key, $folder);
		$this->shouldThrow($exception)->duringInstantiation();
	}

	public function it_always_gets_default_value_if_cache_folder_is_null()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$defaultValue = 123;

		$this->beConstructedWith($key, $folder);
		$this->get($key, $defaultValue)->shouldBe($defaultValue);
	}

	public function it_always_gets_default_value_for_keys_other_than_specified()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$otherKey = 'other_key';
		$defaultValue = 123;

		$this->beConstructedWith($otherKey, $folder);
		$this->shouldBeAnInstanceOf(PH\Cache\DataFilePerKey::class);//Call constructor
		\file_put_contents(self::TEST_CACHE_FILE, self::TEST_CACHE_CONTENT);
		$this->get($key, $defaultValue)->shouldBe($defaultValue);
	}


	public function it_gets_default_value_for_specified_key_if_cache_is_empty()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$defaultValue = 123;

		$this->beConstructedWith($key, $folder);
		$this->get($key, $defaultValue)->shouldBe($defaultValue);
	}

	public function it_gets_cached_value_for_specified_key_stored_in_file()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$defaultValue = 123;

		$this->beConstructedWith($key, $folder);
		$this->shouldBeAnInstanceOf(PH\Cache\DataFilePerKey::class);//Call constructor
		\file_put_contents(self::TEST_CACHE_FILE, self::TEST_CACHE_CONTENT);
		$this->get($key, $defaultValue)->shouldBe(self::TEST_CACHE_DATA);
	}

	public function it_never_sets_value_to_cache_if_cache_folder_is_null()
	{
		$key = self::TEST_CACHE_KEY;
		$value = self::TEST_CACHE_DATA;

		$this->beConstructedWith($key, null);
		$this->set($key, $value)->shouldBe(false);
	}

	public function it_never_sets_value_to_cache_for_keys_other_than_specified()
	{
		$key = self::TEST_CACHE_KEY;
		$otherKey = 'other_key';
		$folder = self::TEST_CACHE_FOLDER;
		$value = self::TEST_CACHE_DATA;

		$this->beConstructedWith($otherKey, $folder);
		$this->set($key, $value)->shouldBe(false);
	}

	public function it_never_sets_non_array_value_to_cache()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$value = 123;

		$this->beConstructedWith($key, $folder);
		$this->set($key, $value)->shouldBe(false);
	}

	public function it_never_sets_value_to_cache_with_ttl()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$value = self::TEST_CACHE_DATA;

		$this->beConstructedWith($key, $folder);
		$this->set($key, $value, 1)->shouldBe(false);
	}

	public function it_does_not_set_value_to_cache_if_it_can_not_save_value_to_temporary_file()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$value = self::TEST_CACHE_DATA;
		$temporaryFile = 'temp_file';

		GlobalFunctionMock::shouldReceive('tempnam')->withArgs(
			function (string $directory, string $prefix) use ($folder)
			{
				return (($directory === \realpath($folder)) && ($prefix === 'swap'));
			}
		)->andReturn($temporaryFile);
		GlobalFunctionMock::shouldReceive('file_put_contents')->with($temporaryFile, self::TEST_CACHE_CONTENT)->andReturn(false);
		$this->beConstructedWith($key, $folder);
		$this->set($key, $value)->shouldBe(false);
	}

	public function it_does_not_set_value_to_cache_if_it_can_not_move_temporary_file_to_permanent_location()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$file = self::TEST_CACHE_FILE;
		$value = self::TEST_CACHE_DATA;
		$temporaryFile = 'temp_file';

		GlobalFunctionMock::shouldReceive('tempnam')->withArgs(
			function (string $directory, string $prefix) use ($folder)
			{
				return (($directory === \realpath($folder)) && ($prefix === 'swap'));
			}
		)->andReturn($temporaryFile);
		GlobalFunctionMock::shouldReceive('file_put_contents')->with($temporaryFile, self::TEST_CACHE_CONTENT)->andReturn(true);
		GlobalFunctionMock::shouldReceive('chmod')->with($temporaryFile, 0664)->andReturn(true);
		GlobalFunctionMock::shouldReceive('rename')->withArgs(
			function (string $from, string $to) use ($temporaryFile, $folder, $file)
			{
				return (($from === $temporaryFile) && ($to === \str_replace($folder, \realpath($folder), $file)));
			}
		)->andReturn(false);
		$this->beConstructedWith($key, $folder);
		$this->set($key, $value)->shouldBe(false);
	}

	public function it_sets_value_to_cache_by_storing_in_file()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$value = self::TEST_CACHE_DATA;

		$this->beConstructedWith($key, $folder);
		$this->set($key, $value)->shouldBe(true);
		$this->get($key)->shouldBe($value);
	}

	public function it_throws_on_unused_psr16_methods()
	{
		$key = self::TEST_CACHE_KEY;
		$folder = self::TEST_CACHE_FOLDER;
		$value = self::TEST_CACHE_DATA;
		$exception = new \LogicException('Not implemented');

		$this->beConstructedWith($key, $folder);
		$this->shouldThrow($exception)->during('delete', [$key]);
		$this->shouldThrow($exception)->during('has', [$key]);
		$this->shouldThrow($exception)->during('getMultiple', [[$key]]);
		$this->shouldThrow($exception)->during('setMultiple', [[$key => $value]]);
		$this->shouldThrow($exception)->during('deleteMultiple', [[$key]]);
		$this->shouldThrow($exception)->during('clear');
	}
}
