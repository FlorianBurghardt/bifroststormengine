<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\support\tools;

use de\bifroststormengine\support\tools\ClassLoader;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class ClassLoaderTest extends TestKernel
{
	#region public tests
	public function testClassLoaderLoadsItsOwnClass(): void
	{
		$this->assertTrue(
			\class_exists(ClassLoader::class),
			"ClassLoader ClassLoader could not load itself."
		);
	}

	public function testRegisterStoresPrefixes(): void
	{
		ClassLoader::register([
			'Test\\' => __DIR__,
		]);

		$prefixes = ClassLoader::getRegisteredPrefixes();

		$this->assertTrue(
			isset($prefixes['Test\\']),
			'Namespace-Prefix is not stored in ClassLoader.'
		);

		$this->assertEquals(
			__DIR__,
			ClassLoader::getBaseDirForPrefix('Test\\'),
			'Basedirectory for Namespace-Prefix is not correct.'
		);
	}
	#endregion
}