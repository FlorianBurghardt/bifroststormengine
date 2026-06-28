<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\example;

use de\bifroststormengine\tests\TestKernel;
#endregion

/**
 * Example test class to demonstrate the BifrostStormEngine test framework.
 *
 * Shows:
 * - A simple test without parameters
 * - A parameterized test using cartesianParameters(...)
 * - A parameterized test with explicitly defined static data sets
 */
final class ExampleTest extends TestKernel
{
	#region public tests
	/**
	 * Simple test without any parameters.
	 */
	public function testWithoutParameters(): void
	{
		$this->assertEquals(5, 2 + 3, "Basic addition should work.");
	}

	/**
	 * Parameterized test:
	 * - $country will be one of: AT, DE, IT
	 * - $email will be one of: test1@gmail.com, test2@gmx.de
	 *
	 * The combinations are generated via dataTestWithCartesianParameters()
	 * using TestKernel::cartesianParameters().
	 */
	public function testWithCartesianParameters(string $country, string $email): void
	{
		$this->assertTrue(
			\in_array($country, ['AT', 'DE', 'IT'], true),
			"Country {$country} is not supported."
		);

		$this->assertTrue(
			\str_contains($email, '@'),
			"Email {$email} is not valid."
		);
	}

	/**
	 * Data provider for testWithCartesianParameters().
	 *
	 * Here we use cartesianParameters() to generate all combinations of the provided arrays.
	 * @example Important to know: It is not only possible to handover 2 arrays.
	 * You can handover any number of arrays to cartesianParameters() - it will generate the cartesian product of all of them.
	 * self::cartesianParameters($countries, $emails, $otherArray, ...)
	 *
	 * @return array<int, array{0: string, 1: string}>
	 */
	public function dataTestWithCartesianParameters(): array
	{
		$countries = ['AT', 'DE', 'IT'];
		$emails    = ['test1@gmail.com', 'test2@gmx.de'];

		// This will generate 3 x 2 = 6 test cases
		return self::cartesianParameters($countries, $emails);
	}

	/**
	 * Parameterized test with explicitly defined static parameter sets.
	 */
	public function testWithStaticParameters(string $country, string $email): void
	{
		$this->assertTrue(
			\in_array($country, ['AT', 'DE'], true),
			"Country {$country} is not supported."
		);

		$this->assertTrue(
			\str_contains($email, '@'),
			"Email {$email} is not valid."
		);
	}

	/**
	 * Data provider for testWithStaticParameters().
	 *
	 * Here we define the test cases explicitly instead of using
	 * cartesianParameters().
	 *
	 * @return array<int, array{0: string, 1: string}>
	 */
	public function dataTestWithStaticParameters(): array
	{
		return [
			['AT', 'test1@gmail.com'],
			['DE', 'test1@gmail.com'],
		];
	}
	#endregion
}