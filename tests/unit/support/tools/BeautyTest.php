<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\support\tools;

use de\bifroststormengine\support\tools\Beauty;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class BeautyTest extends TestKernel
{
	#region public tests
	public function testDetectType(): void
	{
		$this->assertEquals('null',    Beauty::detectType(null));
		$this->assertEquals('boolean', Beauty::detectType(true));
		$this->assertEquals('integer', Beauty::detectType(5));
		$this->assertEquals('float',   Beauty::detectType(1.23));
		$this->assertEquals('string',  Beauty::detectType('abc'));
		$this->assertEquals('array',   Beauty::detectType([]));
		$this->assertEquals('object',  Beauty::detectType((object)['a' => 1]));
	}

	public function testCssIncludedOnlyOnce(): void
	{
		Beauty::resetForTests();

		\ob_start();
		Beauty::dump("Hello");
		$output1 = \ob_get_clean();

		$this->assertTrue(
			\str_contains($output1, '<style>'),
			"Beim ersten Aufruf muss CSS vorhanden sein."
		);

		\ob_start();
		Beauty::dump("Again");
		$output2 = \ob_get_clean();

		$this->assertFalse(
			\str_contains($output2, '<style>'),
			"Beim zweiten Aufruf darf CSS NICHT erneut ausgegeben werden."
		);
	}

	public function testDumpProducesSomeOutput(): void
	{
		\ob_start();
		Beauty::dump(['a' => 1, 'b' => 2]);
		$output = \ob_get_clean();

		$this->assertNotNull($output, "Dump erzeugt keine Ausgabe.");
		$this->assertTrue(
			\str_contains($output, 'a'),
			"Dump-Ausgabe scheint nicht den Inhalt zu enthalten."
		);
	}
	#endregion
}