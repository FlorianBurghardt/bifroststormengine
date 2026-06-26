<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests;

use de\bifroststormengine\tests\Enum\TestExceptionType;
use de\bifroststormengine\tests\Exception\TestException;
use Throwable;
#endregion

abstract class TestKernel
{
	#region public methods (hooks)
	/**
	 * Called once before each test method.
	 */
	public function setUp(): void {/* Default: do nothing */}

	/**
	 * Called once after each test method.
	 */
	public function tearDown(): void {/* Default: do nothing */}

	/**
	 * Called once before the first test method of this class.
	 */
	public static function setUpBeforeClass(): void {/* Default: do nothing */}

	/**
	 * Called once after the last test method of this class.
	 */
	public static function tearDownAfterClass(): void {/* Default: do nothing */}
	#endregion

	#region protected methods (assertions)
	protected function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
	{
		if ($expected !== $actual)
		{
			throw new TestException(
				TestExceptionType::ASSERTION_EQUALS,
				TestExceptionType::ASSERTION_EQUALS->value,
				"assertEquals failed: {$message}\nExpected: " .
					\var_export($expected, true) .
					"\nActual: " .
					\var_export($actual, true)
			);
		}
	}

	protected function assertNotEquals(mixed $expected, mixed $actual, string $message = ''): void
	{
		if ($expected === $actual)
		{
			throw new TestException(
				TestExceptionType::ASSERTION_NOT_EQUALS,
				TestExceptionType::ASSERTION_NOT_EQUALS->value,
				"assertNotEquals failed: {$message}\nBoth values are equal: " .
					\var_export($actual, true)
			);
		}
	}

	protected function assertSame(mixed $expected, mixed $actual, string $message = ''): void
	{
		if ($expected !== $actual)
		{
			throw new TestException(
				TestExceptionType::ASSERTION_SAME,
				TestExceptionType::ASSERTION_SAME->value,
				"assertSame failed: {$message}\nExpected (same): " .
					\var_export($expected, true) .
					"\nActual: " .
					\var_export($actual, true)
			);
		}
	}

	protected function assertTrue(bool $condition, string $message = ''): void
	{
		if (!$condition)
		{
			throw new TestException(
				TestExceptionType::ASSERTION_TRUE,
				TestExceptionType::ASSERTION_TRUE->value,
				"assertTrue failed: {$message}"
			);
		}
	}

	protected function assertFalse(bool $condition, string $message = ''): void
	{
		if ($condition)
		{
			throw new TestException(
				TestExceptionType::ASSERTION_FALSE,
				TestExceptionType::ASSERTION_FALSE->value,
				"assertFalse failed: {$message}"
			);
		}
	}

	protected function assertNull(mixed $value, string $message = ''): void
	{
		if ($value !== null)
		{
			throw new TestException(
				TestExceptionType::ASSERTION_NULL,
				TestExceptionType::ASSERTION_NULL->value,
				"assertNull failed: {$message}\nExpected null\nActual: " .
					\var_export($value, true)
			);
		}
	}

	protected function assertNotNull(mixed $value, string $message = ''): void
	{
		if ($value === null)
		{
			throw new TestException(
				TestExceptionType::ASSERTION_NOT_NULL,
				TestExceptionType::ASSERTION_NOT_NULL->value,
				"assertNotNull failed: {$message}"
			);
		}
	}

	protected function assertInstanceOf(string $type, mixed $object, string $message = ''): void
	{
		if (!($object instanceof $type))
		{
			throw new TestException(
				TestExceptionType::ASSERTION_INSTANCE_OF,
				TestExceptionType::ASSERTION_INSTANCE_OF->value,
				"assertInstanceOf failed: {$message}\nExpected instance of $type.\nGot: " .
					\get_debug_type($object)
			);
		}
	}

	protected function assertThrows(callable $callback, string $expectedType, string $message = ''): void
	{
		try
		{
			$callback();
		}
		catch (Throwable $e)
		{
			if (!($e instanceof $expectedType))
			{
				throw new TestException(
					TestExceptionType::ASSERTION_THROWS,
					TestExceptionType::ASSERTION_THROWS->value,
					"assertThrows failed: {$message}\nExpected exception of type {$expectedType}, got " .
						\get_class($e) . " with message: " . $e->getMessage(),
					$e
				);

			}
			return; // Test passed
		}

		throw new TestException(
			TestExceptionType::ASSERTION_THROWS,
			TestExceptionType::ASSERTION_THROWS->value + 10, // Different code for no exception thrown
			"assertThrows failed: {$message}\nNo exception was thrown.",

		);
	}

	protected function fail(string $message = ''): void
	{
		throw new TestException(
			TestExceptionType::ASSERTION_FAILED,
			TestExceptionType::ASSERTION_FAILED->value,
			"Test failed explicitly: {$message}"
		);
	}
	#endregion

	#region protected static methods (helper methods)
	/**
	 * Build a Cartesian product of the given parameter arrays.
	 *
	 * Example:
	 *  $countries = ['AT', 'DE', 'IT'];
	 *  $emails    = ['test1@gmail.com', 'test2@gmx.de'];
	 *
	 *  self::cartesianParameters($countries, $emails) =>
	 *      [
	 *          ['AT', 'test1@gmail.com'],
	 *          ['DE', 'test1@gmail.com'],
	 *          ['IT', 'test1@gmail.com'],
	 *          ['AT', 'test2@gmail.com'],
	 *          ['DE', 'test2@gmail.com'],
	 *          ['IT', 'test2@gmail.com'],
	 *      ]
	 *
	 * @param array<mixed> ...$parameterLists
	 * @return array<int, array<int, mixed>>
	 */
	protected static function cartesianParameters(array ...$parameterLists): array
	{
		$result = [[]];

		foreach ($parameterLists as $list)
		{
			$append = [];

			foreach ($result as $product)
			{
				foreach ($list as $value)
				{
					$append[] = [...$product, $value];
				}
			}
			$result = $append;
		}
		return $result;
	}
	#endregion
}