<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core\environment;

use de\bifroststormengine\core\environment\Environment;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class EnvironmentTest extends TestKernel
{
	#region tests
	public function testEnumValues(): void
	{
		$this->assertEquals('dev', Environment::DEV->value);
		$this->assertEquals('prod', Environment::PROD->value);
		$this->assertEquals('test', Environment::TEST->value);
	}
	#endregion
}