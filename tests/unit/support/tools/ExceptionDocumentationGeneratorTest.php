<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\support\tools;

use de\bifroststormengine\support\tools\ExceptionDocumentationGenerator;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class ExceptionDocumentationGeneratorTest extends TestKernel
{
	#region properties
	private string $baseDir;
	#endregion

	#region public methods (hooks)
	public function setUp(): void
	{
		$this->baseDir = \sys_get_temp_dir() . '/doc_builder_' . \uniqid();

		$path = $this->baseDir . '/de/bifroststormengine/example';
		\mkdir($path, 0777, true);

		\file_put_contents(
			$path . '/ExampleClass.php',
			<<<'PHP'
				<?php

				namespace de\bifroststormengine\example;

				use de\bifroststormengine\core\Exception\FrameworkException;
				use de\bifroststormengine\core\Enum\ExceptionType;

				final class ExampleClass
				{
					public function test(): void
					{
						throw new FrameworkException(ExceptionType::NOT_FOUND, 1001, "Something bad", 9999);
					}
				}
			PHP
		);
	}

	public function tearDown(): void
	{
		$this->removeDir($this->baseDir);
	}
	#endregion

	#region public tests
	public function testExceptionDocumentationGenerator(): void
	{
		$builder = new ExceptionDocumentationGenerator();
		$result = $builder->build($this->baseDir, 'de');

		$this->assertEquals(1, \count($result));

		$entry = $result[0];

		$this->assertEquals('de\\bifroststormengine\\example', $entry['Namespace']);
		$this->assertEquals('ExampleClass', $entry['Class']);
		$this->assertEquals('NOT_FOUND', $entry['ErrorType']);
		$this->assertEquals(1001, $entry['ErrorCode']);
		$this->assertEquals('Something bad', $entry['Message']);
		$this->assertEquals(9999, $entry['PreviousErrorCode']);
	}

	public function testBuildAsJsonReturnsValidJson(): void
	{
		$builder = new ExceptionDocumentationGenerator();

		$json = $builder->buildAsJson($this->baseDir, 'de');

		$this->assertTrue(
			\is_string($json) && $json !== '',
			'buildAsJson should return a non-empty JSON string'
		);

		$decoded = json_decode($json, true);

		$this->assertTrue(
			\is_array($decoded),
			'JSON returned by buildAsJson should decode to an array'
		);
	}
	#endregion

	#region private methods
	private function removeDir(string $dir): void
	{
		if (!\is_dir($dir)) return;

		foreach (\scandir($dir) as $f)
		{
			if ($f === '.' || $f === '..') continue;

			$p = $dir . '/' . $f;
			if (\is_dir($p)) $this->removeDir($p);
			else \unlink($p);
		}
		\rmdir($dir);
	}
	#endregion
}