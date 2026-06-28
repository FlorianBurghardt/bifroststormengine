<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core\DTO;

use de\bifroststormengine\core\DTO\FrameworkManifest;
use de\bifroststormengine\core\Framework;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class FrameworkManifestTest extends TestKernel
{
	#region public tests
	public function testConstructorInitializesWithFrameworkDefaults(): void
	{
		$manifest = new FrameworkManifest();

		$this->assertEquals(Framework::getName(), $manifest->name);
		$this->assertEquals(Framework::VERSION, $manifest->version);
		$this->assertEquals(PHP_VERSION, $manifest->phpVersion);
		$this->assertNotNull($manifest->buildTimestamp);
		$this->assertTrue(
			$this->isIso8601($manifest->buildTimestamp),
			'buildTimestamp should be a valid ISO-8601 datetime.'
		);
		$this->assertNull($manifest->gitCommit);
		$this->assertEquals([], $manifest->modules);
	}

	public function testToArrayContainsExpectedKeys(): void
	{
		$manifest = new FrameworkManifest();
		$manifest->gitCommit = 'abc123';

		$array = $manifest->toArray();

		$this->assertEquals($manifest->name, $array['name']);
		$this->assertEquals($manifest->version, $array['version']);
		$this->assertEquals($manifest->phpVersion, $array['phpVersion']);
		$this->assertEquals($manifest->buildTimestamp, $array['buildTimestamp']);
		$this->assertEquals($manifest->gitCommit, $array['gitCommit']);

		// modules are intentionally not part of the array yet (TODO)
		$this->assertFalse(
			\array_key_exists('modules', $array),
			'modules should not yet be exported by toArray().'
		);
	}
	#endregion

	#region private methods
	private function isIso8601(string $value): bool
	{
		$pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+\-]\d{2}:\d{2})$/';
		return (bool)\preg_match($pattern, $value);
	}
	#endregion
}