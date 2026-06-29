<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core\config;

use de\bifroststormengine\core\config\Config;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class ConfigTest extends TestKernel
{
	#region tests
	public function testGetReturnsValue(): void
	{
		$config = new Config(['a' => 1]);

		$this->assertEquals(1, $config->get('a'));
	}

	public function testGetReturnsDefault(): void
	{
		$config = new Config([]);

		$this->assertEquals('default', $config->get('x', 'default'));
	}

	public function testHasWorks(): void
	{
		$config = new Config(['a' => 1]);

		$this->assertTrue($config->has('a'));
		$this->assertFalse($config->has('b'));
	}
	#endregion
}