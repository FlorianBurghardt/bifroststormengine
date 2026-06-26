<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core;

use de\bifroststormengine\core\DTO\FrameworkManifest;
use de\bifroststormengine\core\Framework;
use de\bifroststormengine\core\FrameworkManifestProvider;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class FrameworkManifestProviderTest extends TestKernel
{
	#region properties
	private string $tempDir;
	#endregion

	#region public methods (hooks)
	public function setUp(): void
	{
		$this->tempDir = \sys_get_temp_dir() . '/bifrost_manifest_tests_' . \uniqid('', true);
		\mkdir($this->tempDir, 0777, true);
	}

	public function tearDown(): void
	{
		foreach (\glob($this->tempDir . '/*') as $file)
		{
			@\unlink($file);
		}
		@\rmdir($this->tempDir);
	}
	#endregion

	#region public tests
	public function testLoadFromPathReturnsDefaultManifestIfFileDoesNotExist(): void
	{
		$path = $this->tempDir . '/non_existing_manifest.json';

		$manifest = FrameworkManifestProvider::loadFromPath($path);

		$this->assertInstanceOf(
			FrameworkManifest::class,
			$manifest,
			'loadFromPath() should always return a FrameworkManifest instance.'
		);

		$this->assertEquals(Framework::getName(), $manifest->name, 'Default name should use Framework::getName().');
		$this->assertEquals(Framework::VERSION, $manifest->version, 'Default version should use Framework::VERSION.');
		$this->assertEquals(PHP_VERSION, $manifest->phpVersion, 'Default phpVersion should use PHP_VERSION.');
		$this->assertNotNull($manifest->buildTimestamp, 'Default buildTimestamp should not be null.');
		$this->assertIsIso8601($manifest->buildTimestamp, 'Default buildTimestamp should be a valid ISO-8601 string.');
		$this->assertNull($manifest->gitCommit, 'Default gitCommit should be null.');
		$this->assertEquals([], $manifest->modules, 'Default modules should be an empty array.');
	}

	public function testLoadFromPathReturnsDefaultManifestOnInvalidJson(): void
	{
		$path = $this->tempDir . '/invalid_manifest.json';
		\file_put_contents($path, '{ invalid json ...');

		$manifest = FrameworkManifestProvider::loadFromPath($path);

		$this->assertEquals(Framework::getName(), $manifest->name);
		$this->assertEquals(Framework::VERSION, $manifest->version);
		$this->assertEquals(PHP_VERSION, $manifest->phpVersion);
		$this->assertIsIso8601($manifest->buildTimestamp);
		$this->assertNull($manifest->gitCommit);
		$this->assertEquals([], $manifest->modules);
	}

	public function testLoadFromPathParsesValidManifestCorrectly(): void
	{
		$path = $this->tempDir . '/valid_manifest.json';

		$data = [
			'name'           => 'Bifrost StormEngine PROD',
			'version'        => '1.0.0-rc1',
			'phpVersion'     => '8.2.0',
			'buildTimestamp' => '2026-03-12T20:30:00+00:00',
			'gitCommit'      => 'abc123def',
			'modules'        => ['core', 'http', 'support'],
		];

		\file_put_contents($path, \json_encode($data, JSON_PRETTY_PRINT));

		$manifest = FrameworkManifestProvider::loadFromPath($path);

		$this->assertEquals($data['name'], $manifest->name);
		$this->assertEquals($data['version'], $manifest->version);
		$this->assertEquals($data['phpVersion'], $manifest->phpVersion);
		$this->assertEquals($data['buildTimestamp'], $manifest->buildTimestamp);
		$this->assertEquals($data['gitCommit'], $manifest->gitCommit);
		$this->assertEquals($data['modules'], $manifest->modules);
	}

	public function testLoadFromPathUsesDefaultsForMissingOptionalFields(): void
	{
		$path = $this->tempDir . '/partial_manifest.json';

		$data = [
			'name'       => 'Bifrost StormEngine STAGING',
			'version'    => '1.0.0-rc2',
			'phpVersion' => '8.2.1',
		];

		\file_put_contents($path, \json_encode($data, JSON_PRETTY_PRINT));

		$manifest = FrameworkManifestProvider::loadFromPath($path);

		$this->assertEquals($data['name'], $manifest->name);
		$this->assertEquals($data['version'], $manifest->version);
		$this->assertEquals($data['phpVersion'], $manifest->phpVersion);

		$this->assertNotNull($manifest->buildTimestamp);
		$this->assertIsIso8601($manifest->buildTimestamp);

		$this->assertNull($manifest->gitCommit);

		$this->assertEquals([], $manifest->modules);
	}
	#endregion

	#region private methods
	/**
	 * Helper assertion to check ISO-8601 datetime format.
	 */
	private function assertIsIso8601(string $value, string $message = ''): void
	{
		$pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+\-]\d{2}:\d{2})$/';

		$this->assertTrue(
			(bool)\preg_match($pattern, $value),
			$message !== '' ? $message : "Value '$value' is not a valid ISO-8601 datetime."
		);
	}
	#endregion
}