<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core\bootstrap;

use de\bifroststormengine\core\bootstrap\MiddlewareBuilder;
use de\bifroststormengine\core\config\Config;
use de\bifroststormengine\tests\fixtures\middleware\TestMiddleware;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class MiddlewareBuilderTest extends TestKernel
{
	#region public tests
	public function testBuildValidMiddleware(): void
	{
		$config = new Config([
			'middleware' => [
				TestMiddleware::class
			]
		]);

		$builder = new MiddlewareBuilder($config);

		$result = $builder->build();

		$this->assertCount(1, $result);
		$this->assertInstanceOf(TestMiddleware::class, $result[0]);
	}

	public function testInvalidClassThrows(): void
	{
		$config = new Config([
			'middleware' => ['Does\Not\Exist']
		]);

		$builder = new MiddlewareBuilder($config);

		$this->assertThrows(
			fn() => $builder->build(),
			\RuntimeException::class
		);
	}

	public function testInvalidTypeThrows(): void
	{
		$config = new Config([
			'middleware' => [\stdClass::class]
		]);

		$builder = new MiddlewareBuilder($config);

		$this->assertThrows(
			fn() => $builder->build(),
			\RuntimeException::class
		);
	}

	public function testNonArrayMiddlewareThrows(): void
	{
		$config = new Config([
			'middleware' => 'invalid' // ❌ not an array
		]);

		$builder = new MiddlewareBuilder($config);

		$this->assertThrows(
			fn() => $builder->build(),
			\RuntimeException::class,
			'Config key "middleware" must be an array'
		);
	}

	public function testEmptyMiddlewareReturnsEmptyArray(): void
	{
		$config = new Config([]);

		$builder = new MiddlewareBuilder($config);

		$result = $builder->build();

		$this->assertCount(0, $result);
	}
	#endregion
}