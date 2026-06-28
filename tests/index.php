<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests;

use de\bifroststormengine\core\Enum\PHPExceptionType;
use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\support\tools\Beauty;
use de\bifroststormengine\support\tools\ClassLoader;
use de\bifroststormengine\tests\Exception\TestException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Throwable;
#endregion

#region feature flags
\define('USE_BEAUTY_OUTPUT', true);
\define('DO_UNIT_TESTS', true);
\define('DO_INTEGRATION_TESTS', true);
\define('DO_EXAMPLE_TESTS', false);
#endregion

#region namespace registration
require __DIR__ . '/../../../de/bifroststormengine/support/tools/ClassLoader.php';

ClassLoader::register([
	'de\\bifroststormengine\\' => \realpath(__DIR__ . '/../../../de/bifroststormengine')
]);
#endregion

#region test runner
print_out("Running BifrostStormEngine tests...", "Test runner started.");

$totalPassed = 0;
$totalFailed = 0;

if (DO_UNIT_TESTS)
{
	[$p, $f] = run_tests_in_directory(__DIR__ . '/unit', 'Running UNIT tests');
	$totalPassed += $p;
	$totalFailed += $f;
}

if (DO_INTEGRATION_TESTS)
{
	[$p, $f] = run_tests_in_directory(__DIR__ . '/integration', 'Running INTEGRATION tests');
	$totalPassed += $p;
	$totalFailed += $f;
}

if (DO_EXAMPLE_TESTS)
{
	[$p, $f] = run_tests_in_directory(__DIR__ . '/example', 'Running EXAMPLE tests');
	$totalPassed += $p;
	$totalFailed += $f;
}

$output = [];
$output[] = "Global-Result:";
$output[] = "  ✔ Passed: {$totalPassed}";
$output[] = "  ✘ Failed: {$totalFailed}";

print_out($output, "Tests finished");
#endregion

#region helper functions
function run_tests_in_directory(string $directory, string $groupTitle): array
{
	$outputGroup = [];
	$passed = 0;
	$failed = 0;

	if (!\is_dir($directory))
	{
		$outputGroup[] = "[WARN] Test directory not found: {$directory}";
		print_out($outputGroup, $groupTitle);
		return [$passed, $failed];
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($directory)
	);

	foreach ($iterator as $file)
	{

		$passedInner = 0;
		$failedInner = 0;
		$output = [];

		if (!$file->isFile() || $file->getExtension() !== 'php')
		{
			continue;
		}

		require_once $file->getRealPath();

		$relativePath = \str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $file->getRealPath());
		$class = 'de\\bifroststormengine\\tests\\' . \str_replace(
			[DIRECTORY_SEPARATOR, '.php'],
			['\\', ''],
			$relativePath
		);

		if (!\class_exists($class))
		{
			$output[] = "[WARN] {$class} not found. File: {$file->getFilename()}";
			print_out($output, "Test class finished: ({$file->getFilename()})");
			continue;
		}

		$testObject  = new $class();
		$reflection  = new ReflectionClass($class);
		$methodsInfo = $reflection->getMethods();

		if (\method_exists($class, 'setUpBeforeClass'))
		{
			$class::setUpBeforeClass();
		}

		foreach ($methodsInfo as $methodInfo)
		{

			$method = $methodInfo->getName();

			if (!\str_starts_with($method, 'test'))
			{
				continue;
			}

			$parameterCount = $methodInfo->getNumberOfParameters();

			$dataSets = [[]];

			if ($parameterCount > 0)
			{
				$dataProviderName = 'data' . \ucfirst($method);

				if (!$reflection->hasMethod($dataProviderName))
				{
					throw new FrameworkException(
						PHPExceptionType::RUNTIME_ERROR,
						60000,
						"Test method {$class}::{$method} expects {$parameterCount} parameter(s) " .
						"but no data provider method {$dataProviderName}() was found."
					);
				}

				$providerMethod = $reflection->getMethod($dataProviderName);

				if (!$providerMethod->isPublic())
				{
					throw new FrameworkException(
						PHPExceptionType::RUNTIME_ERROR,
						60001,
						"Data provider {$class}::{$dataProviderName}() must be public."
					);
				}

				$dataSets = $providerMethod->invoke($testObject);

				if (!\is_array($dataSets))
				{
					throw new FrameworkException(
						PHPExceptionType::RUNTIME_ERROR,
						60002,
						"Data provider {$class}::{$dataProviderName}() must return an array."
					);
				}

				$normalized = [];
				foreach ($dataSets as $index => $dataRow)
				{
					if (!\is_array($dataRow))
					{
						$dataRow = [$dataRow];
					}

					if (\count($dataRow) !== $parameterCount)
					{
						throw new FrameworkException(
							PHPExceptionType::RUNTIME_ERROR,
							60003,
							"Data provider {$class}::{$dataProviderName}() returned a row " .
							"with " . \count($dataRow) . " value(s), but {$parameterCount} parameter(s) are required " .
							"for {$class}::{$method}() (row index: {$index})."
						);
					}
					$normalized[] = $dataRow;
				}
				$dataSets = $normalized;
			}
			$caseIndex = 0;

			foreach ($dataSets as $args)
			{
				$caseIndex++;

				try
				{
					if (\method_exists($testObject, 'setUp'))
					{
						$testObject->setUp();
					}

					$testObject->$method(...$args);

					$passedInner++;
					$output[] = "[OK]   {$method} #{$caseIndex} " . format_test_args($args);
				}
				catch (Throwable $e)
				{
					$failedInner++;

					if ($e instanceof TestException)
					{
						$output[] =
							"[FAIL] {$method} #{$caseIndex} " .
							format_test_args($args) .
							" (Assertion: {$e->getType()->name}) Message: {$e->getMessage()}";
					}
					else
					{
						$output[] =
							"[ERROR] {$method} #{$caseIndex} " .
							format_test_args($args) .
							" Unexpected exception " . get_class($e) . ": {$e->getMessage()}";
					}
				}
				finally
				{
					if (\method_exists($testObject, 'tearDown'))
					{
						$testObject->tearDown();
					}
				}
			}
		}

		if (\method_exists($class, 'tearDownAfterClass'))
		{
			$class::tearDownAfterClass();
		}

		$output[] = "Class-Result:";
		$output[] = "  ✔ Passed: {$passedInner}";
		$output[] = "  ✘ Failed: {$failedInner}";

		print_out($output, "Test class finished: ({$file->getFilename()})");

		$passed += $passedInner;
		$failed += $failedInner;
	}
	return [$passed, $failed];
}

function print_out(string|array $text, string $title): void
{
	if (USE_BEAUTY_OUTPUT)
	{
		Beauty::dump($text, $title);
	}
	else
	{
		if (\is_array($text))
		{
			foreach ($text as $line)
			{
				echo $line . "<br/>\n";
			}
		}
		else
		{
			echo $text . "<br/>\n";
		}
	}
}

function format_test_args(array $args): string
{
	if (empty($args))
	{
		return '';
	}

	$parts = [];

	foreach ($args as $arg)
	{
		if (\is_scalar($arg) || $arg === null)
		{
			$parts[] = \var_export($arg, true);
		}
		else
		{
			$parts[] = \get_debug_type($arg);
		}
	}
	return '(' . \implode(', ', $parts) . ')';
}
#endregion