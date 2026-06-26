<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\support\filesystem;

use de\bifroststormengine\support\filesystem\FileSystemExplorer;
use de\bifroststormengine\support\filesystem\Options\FileSearchOptions;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class FileSystemExplorerTest extends TestKernel
{
	#region properties
	private string $baseDir;
	#endregion

	#region public methods (hooks)
	public function setUp(): void
	{
		parent::setUp();

		$this->baseDir = \sys_get_temp_dir()
			. DIRECTORY_SEPARATOR
			. 'bifrost_fs_explorer_test_'
			. \uniqid('', true);

		$this->createTestFilesystem();
	}

	public function tearDown(): void
	{
		$this->removeDirectory($this->baseDir);
		parent::tearDown();
	}
	#endregion

	#region public tests
	public function testScanWithOptionsReturnsEmptyArrayForInvalidDirectory(): void
	{
		$options = new FileSearchOptions();
		$result = FileSystemExplorer::scanWithOptions(
			$this->baseDir . '_does_not_exist',
			$options
		);

		$this->assertEquals([], $result, 'Expected empty array for invalid directory');
	}

	public function testScanNonRecursiveFindsOnlyTopLevelFiles(): void
	{
		$options = (new FileSearchOptions())
			->withRecursive(false);

		$files = FileSystemExplorer::scanWithOptions($this->baseDir, $options);

		// Expect only file1.php and file2.txt (top-level)
		$this->assertEquals(
			2,
			\count($files),
			'Non-recursive scan should return exactly 2 files'
		);

		$expected1 = $this->baseDir . DIRECTORY_SEPARATOR . 'file1.php';
		$expected2 = $this->baseDir . DIRECTORY_SEPARATOR . 'file2.txt';

		$this->assertTrue(
			\in_array($expected1, $files, true),
			'file1.php should be in non-recursive scan result'
		);
		$this->assertTrue(
			\in_array($expected2, $files, true),
			'file2.txt should be in non-recursive scan result'
		);
	}

	public function testScanRecursiveFindsFilesInSubdirectories(): void
	{
		$options = (new FileSearchOptions())
			->withRecursive(true);

		$files = FileSystemExplorer::scanWithOptions($this->baseDir, $options);

		$this->assertEquals(
			4,
			\count($files),
			'Recursive scan should return 4 files'
		);

		$expected = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'file1.php',
			$this->baseDir . DIRECTORY_SEPARATOR . 'file2.txt',
			$this->baseDir . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'inner1.php',
			$this->baseDir . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'inner2.md',
		];

		foreach ($expected as $path)
		{
			$this->assertTrue(
				\in_array($path, $files, true),
				'Expected path not found in recursive scan: ' . $path
			);
		}
	}

	public function testScanWithPhpExtensionFilter(): void
	{
		$options = (new FileSearchOptions())
			->withRecursive(true)
			->withExtensions(['php']);

		$files = FileSystemExplorer::scanWithOptions($this->baseDir, $options);
		\sort($files);

		$expected = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'file1.php',
			$this->baseDir . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'inner1.php',
		];
		\sort($expected);

		$this->assertEquals(
			$expected,
			$files,
			'Extension filter ".php" should only return PHP files'
		);
	}

	public function testScanWithIncludePattern(): void
	{
		$options = (new FileSearchOptions())
			->withRecursive(true)
			->withIncludePatterns(['#/sub/#']);

		$files = FileSystemExplorer::scanWithOptions($this->baseDir, $options);
		\sort($files);

		$expected = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'inner1.php',
			$this->baseDir . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'inner2.md',
		];
		\sort($expected);

		$this->assertEquals(
			$expected,
			$files,
			'Include pattern "#/sub/#" should only return files in sub-directory'
		);
	}

	public function testScanWithExcludePattern(): void
	{
		$options = (new FileSearchOptions())
			->withRecursive(true)
			->withExcludePatterns(['#/sub/#']);

		$files = FileSystemExplorer::scanWithOptions($this->baseDir, $options);
		\sort($files);

		$expected = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'file1.php',
			$this->baseDir . DIRECTORY_SEPARATOR . 'file2.txt',
		];
		\sort($expected);

		$this->assertEquals(
			$expected,
			$files,
			'Exclude pattern "#/sub/#" should exclude sub-directory files'
		);
	}

	public function testScanWithExtensionsAndPatternsCombined(): void
	{
		$options = (new FileSearchOptions())
			->withRecursive(true)
			->withExtensions(['php'])
			->withIncludePatterns(['#/sub/#']);

		$files = FileSystemExplorer::scanWithOptions($this->baseDir, $options);

		$this->assertEquals(
			1,
			\count($files),
			'Combined filter should return exactly 1 file'
		);

		$expected = $this->baseDir . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'inner1.php';

		$this->assertEquals(
			$expected,
			$files[0],
			'Combined filter should return only inner1.php from sub-directory'
		);
	}

	public function testTrimPathsWithoutTrimAfter(): void
	{
		$paths = [
			'/var/app/src/Foo.php',
			'/var/app/src/Bar/Baz.php',
			'/var/app/other/Ignore.php',
		];

		$result = FileSystemExplorer::trimPaths($paths, '/src/');

		$expected = [
			'/src/Foo.php',
			'/src/Bar/Baz.php',
			'/var/app/other/Ignore.php',
		];

		$this->assertEquals(
			$expected,
			$result,
			'trimPaths without trimAfter should keep the trim marker'
		);
	}

	public function testTrimPathsWithTrimAfter(): void
	{
		$paths = [
			'/var/app/src/Foo.php',
			'/var/app/src/Bar/Baz.php',
		];

		$result = FileSystemExplorer::trimPaths($paths, '/src/', true);

		$expected = [
			'Foo.php',
			'Bar/Baz.php',
		];

		$this->assertEquals(
			$expected,
			$result,
			'trimPaths with trimAfter did not behave as expected'
		);
	}

	public function testAddPathPrefix(): void
	{
		$paths = [
			'Foo.php',
			'/Bar/Baz.php',
		];

		$result = FileSystemExplorer::addPathPrefix($paths, '/var/app/src');

		$expected = [
			'/var/app/src/Foo.php',
			'/var/app/src/Bar/Baz.php',
		];

		$this->assertEquals(
			$expected,
			$result,
			'addPathPrefix did not behave as expected'
		);
	}
	#endregion

	#region private methods
	private function createTestFilesystem(): void
	{
		// Structure:
		// base/
		//   file1.php
		//   file2.txt
		//   sub/
		//     inner1.php
		//     inner2.md

		\mkdir($this->baseDir, 0777, true);

		\file_put_contents(
			$this->baseDir . DIRECTORY_SEPARATOR . 'file1.php',
			"<?php // test\n"
		);
		\file_put_contents(
			$this->baseDir . DIRECTORY_SEPARATOR . 'file2.txt',
			"text\n"
		);

		$subDir = $this->baseDir . DIRECTORY_SEPARATOR . 'sub';
		\mkdir($subDir);

		\file_put_contents(
			$subDir . DIRECTORY_SEPARATOR . 'inner1.php',
			"<?php // inner\n"
		);
		\file_put_contents(
			$subDir . DIRECTORY_SEPARATOR . 'inner2.md',
			"# markdown\n"
		);
	}

	private function removeDirectory(string $dir): void
	{
		if (!\is_dir($dir)) {
			return;
		}

		$items = \scandir($dir);
		if ($items === false) {
			return;
		}

		foreach ($items as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}

			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if (\is_dir($path)) {
				$this->removeDirectory($path);
			} else {
				@\unlink($path);
			}
		}

		@\rmdir($dir);
	}
	#endregion
}