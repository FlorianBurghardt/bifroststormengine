<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\support\filesystem;

use de\bifroststormengine\support\filesystem\FileContentSearcher;
use de\bifroststormengine\support\filesystem\Options\FileContentSearchOptions;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class FileContentSearcherTest extends TestKernel
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
			. 'bifrost_file_content_searcher_test_'
			. \uniqid('', true);

		$this->createTestFiles();
	}

	public function tearDown(): void
	{
		$this->removeDirectory($this->baseDir);
		parent::tearDown();
	}
	#endregion

	#region public tests
	public function testSearchSingleFileWithKeywordAndStopAt(): void
	{
		$searcher = new FileContentSearcher();

		$options = (new FileContentSearchOptions())
			->withKeywords(['public function doSomething'])
			->withStopAt('}')
			->withIncludeComments(false)
			->withFlattenCode(true)
			->withSkipCommentLines(true);

		$files = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'example1.php',
		];

		$results = $searcher->searchInFiles($files, $options);

		// Expect exactly one file with exactly one match
		$this->assertEquals(
			1,
			\count($results),
			'Expected exactly one FileScanResult for example1.php'
		);

		$result = $results[0];
		$matches = $result->getMatches();

		$this->assertEquals(
			1,
			\count($matches),
			'Expected exactly one FileMatch in example1.php'
		);

		$match = $matches[0];

		// Line number of "public function doSomething..." in $content1:
		// 1: <?php
		// 2:
		// 3: // comment
		// 4: class ExampleOne
		// 5: {
		// 6:     // TODO ...
		// 7:     public function doSomething(): void
		// 8:     {
		// 9:         // implementation
		// 10:        $value = 'test';
		// 11:    }
		// 12: }
		$this->assertEquals(
			7,
			$match->getLine(),
			'Unexpected start line for the matched function'
		);

		$code = $match->getCode();

		$this->assertTrue(
			\str_contains($code, 'public function doSomething'),
			'Code snippet should contain the searched function signature'
		);

		$this->assertFalse(
			\str_contains($code, '//'),
			'Comments should be removed when includeComments=false'
		);

		$this->assertFalse(
			\str_contains($code, "\n"),
			'Code should be flattened into a single line'
		);
	}

	public function testSearchAcrossMultipleFiles(): void
	{
		$searcher = new FileContentSearcher();

		$options = (new FileContentSearchOptions())
			->withKeywords(['$value'])
			->withStopAt(';')            // capture until first semicolon
			->withIncludeComments(true)
			->withFlattenCode(true)
			->withSkipCommentLines(true);

		$files = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'example1.php',
			$this->baseDir . DIRECTORY_SEPARATOR . 'example2.php',
		];

		$results = $searcher->searchInFiles($files, $options);

		// We expect both files to have matches
		$this->assertEquals(
			2,
			\count($results),
			'Expected matches in example1.php and example2.php'
		);

		$fileNames = [];
		foreach ($results as $result)
		{
			$fileNames[] = \basename($result->getFile());
		}
		\sort($fileNames);

		$this->assertEquals(
			['example1.php', 'example2.php'],
			$fileNames,
			'Expected matches in both example files'
		);
	}

	public function testSkipCommentLinesIgnoresKeywordsInPureComments(): void
	{
		$searcher = new FileContentSearcher();

		$options = (new FileContentSearchOptions())
			->withKeywords(['keyword-only-in-comment'])
			->withStopAt(';')
			->withIncludeComments(true)
			->withFlattenCode(true)
			->withSkipCommentLines(true);

		$files = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'comments_only.php',
		];

		$results = $searcher->searchInFiles($files, $options);

		$this->assertEquals(
			0,
			\count($results),
			'Keywords that appear only in pure comment lines should be ignored'
		);
	}

	public function testCaseInsensitiveSearchFindsUpperAndLowerCase(): void
	{
		$searcher = new FileContentSearcher();

		// "exampleone" vs "ExampleOne"
		$options = (new FileContentSearchOptions())
			->withKeywords(['exampleone'])
			->withStopAt('}')
			->withIncludeComments(false)
			->withFlattenCode(true)
			->withCaseSensitive(false);

		$files = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'example1.php',
		];

		$results = $searcher->searchInFiles($files, $options);

		$this->assertEquals(
			1,
			\count($results),
			'Case-insensitive search should find "ExampleOne" using "exampleone"'
		);
	}

	public function testCaseSensitiveSearchDoesNotFindDifferentCase(): void
	{
		$searcher = new FileContentSearcher();

		// "exampleone" vs "ExampleOne", case-sensitive -> no match
		$options = (new FileContentSearchOptions())
			->withKeywords(['exampleone'])
			->withStopAt('}')
			->withIncludeComments(false)
			->withFlattenCode(true)
			->withCaseSensitive(true);

		$files = [
			$this->baseDir . DIRECTORY_SEPARATOR . 'example1.php',
		];

		$results = $searcher->searchInFiles($files, $options);

		$this->assertEquals(
			0,
			\count($results),
			'Case-sensitive search should not find "ExampleOne" using "exampleone"'
		);
	}
	#endregion

	#region private methods
	private function createTestFiles(): void
	{
		\mkdir($this->baseDir, 0777, true);

		// example1.php
		$file1 = $this->baseDir . DIRECTORY_SEPARATOR . 'example1.php';
		$content1 = <<<'PHP'
			<?php

			// This is a comment line and should be ignored
			class ExampleOne
			{
				// TODO: method doSomething
				public function doSomething(): void
				{
					// implementation
					$value = 'test';
				}
			}

			PHP;
		\file_put_contents($file1, $content1);

		// example2.php
		$file2 = $this->baseDir . DIRECTORY_SEPARATOR . 'example2.php';
		$content2 = <<<'PHP'
			<?php

			/* Multi-line comment
			* should also be ignored
			*/
			function helper_function()
			{
				// do stuff
				$value = 42;
			}

			PHP;
		\file_put_contents($file2, $content2);

		// comments_only.php
		$file3 = $this->baseDir . DIRECTORY_SEPARATOR . 'comments_only.php';
		$content3 = <<<'PHP'
			<?php

			// keyword-only-in-comment
			/* another keyword-only-in-comment */

			PHP;
		\file_put_contents($file3, $content3);
	}

	private function removeDirectory(string $dir): void
	{
		if (!\is_dir($dir))
		{
			return;
		}

		$items = \scandir($dir);
		if ($items === false)
		{
			return;
		}

		foreach ($items as $item)
		{
			if ($item === '.' || $item === '..')
			{
				continue;
			}

			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if (\is_dir($path))
			{
				$this->removeDirectory($path);
			}
			else
			{
				@\unlink($path);
			}
		}
		@\rmdir($dir);
	}
	#endregion
}