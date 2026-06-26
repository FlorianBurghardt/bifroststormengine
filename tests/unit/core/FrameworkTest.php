<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core;

use de\bifroststormengine\core\Framework;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class FrameworkTest extends TestKernel
{
	#region tests
	public function testGetNameReturnsExpectedName(): void
	{
		$this->assertEquals(
			'Bifrost StormEngine',
			Framework::getName(),
			'Framework::getName() should return the expected framework name.'
		);
	}

	public function testGetVersionPartsReturnExpectedValues(): void
	{
		$this->assertEquals(1, Framework::getVersionMajor(), 'Major version should be 1.');
		$this->assertEquals(0, Framework::getVersionMinor(), 'Minor version should be 0.');
		$this->assertEquals(0, Framework::getVersionPatch(), 'Patch version should be 0.');
	}

	public function testGetVersionReturnsConcatenatedVersion(): void
	{
		$expected = \sprintf(
			'%d.%d.%d',
			Framework::getVersionMajor(),
			Framework::getVersionMinor(),
			Framework::getVersionPatch()
		);

		$this->assertEquals(
			$expected,
			Framework::getVersion(),
			'Framework::getVersion() should return concatenated major.minor.patch.'
		);
	}

	public function testVersionConstantMatchesGetVersion(): void
	{
		$this->assertEquals(
			Framework::VERSION,
			Framework::getVersion(),
			'Framework::VERSION constant should match Framework::getVersion().'
		);
	}
	#endregion
}