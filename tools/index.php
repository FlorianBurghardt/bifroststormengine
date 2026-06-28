<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine;

use de\bifroststormengine\support\tools\ClassLoader;
use de\bifroststormengine\support\tools\ExceptionDocumentationGenerator;
use de\bifroststormengine\support\tools\FrameworkArchitectureGenerator;
use de\bifroststormengine\support\tools\FrameworkStructureGenerator;
use Throwable;
#endregion

#region namespace registration
require __DIR__ . '/../../../de/bifroststormengine/support/tools/ClassLoader.php';

ClassLoader::register([
	'de\\bifroststormengine\\' => \realpath(__DIR__ . '/../../../de/bifroststormengine'),
]);
#endregion

#region configuration
// ------------------------------------------------------------------
// Configuration
// ------------------------------------------------------------------
$frameworkBasePath = \realpath(__DIR__ . '/../../../de/bifroststormengine') ?: __DIR__ . '/../../../de/bifroststormengine';

$selectedTool = $_GET['tool'] ?? null;
$output       = '';
$error        = null;
#endregion

#region tool execution
if ($selectedTool !== null)
{
	try
	{
		switch ($selectedTool)
		{
			case 'exceptions':
				$builder = new ExceptionDocumentationGenerator();
				$output  = $builder->buildAsJson($frameworkBasePath, 'de');
				break;

			case 'api':
				$generator = new FrameworkArchitectureGenerator();

				$subDirs = ['core', 'http', 'support', 'tests'];
				$excludeSubDirs = ['tests/integration', 'tests/unit'];

				$blocks = [];

				foreach ($subDirs as $subDir)
				{
					$baseDir = $frameworkBasePath . DIRECTORY_SEPARATOR . $subDir;
					if (!\is_dir($baseDir))
					{
						continue;
					}

					$effectiveExcludeDirs = [];
					foreach ($excludeSubDirs as $exclude)
					{
						if (\str_starts_with($exclude, $subDir . '/'))
						{
							$relativeSubPath       = \substr($exclude, \strlen($subDir) + 1);
							$effectiveExcludeDirs[] = $baseDir . DIRECTORY_SEPARATOR . $relativeSubPath;
						}
					}

					$subBlocks = $generator->generate($baseDir, $effectiveExcludeDirs);
					$blocks    = \array_merge($blocks, $subBlocks);
				}

				$output = \implode("\n\n----------------------------------------\n\n", $blocks);
				break;

			case 'structure':
				$generator = new FrameworkStructureGenerator(
					excludedDirectories: ['.git', '.vscode', 'Agent_Refactoring'],
					excludedFiles: ['.gitignore'],
					rootLabel: 'de\\bifroststormengine'
				);

				$output = $generator->generate($frameworkBasePath);
				break;

			default:
				$error = 'Unknown tool selected.';
				break;
		}
	}
	catch (Throwable $e)
	{
		$error = $e->getMessage();
	}
}
#endregion

#region HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Bifrost Tools Dashboard</title>
	<style>
		body {
			font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
			margin: 2rem;
			background-color: #f8f9fa;
		}
		h1 {
			margin-bottom: 0.5rem;
		}
		.subtitle {
			color: #666;
			margin-bottom: 1.5rem;
		}
		form {
			margin-bottom: 1.5rem;
		}
		select, button {
			font-size: 1rem;
			padding: 0.4rem 0.6rem;
		}
		.error {
			color: #b00020;
			margin-bottom: 1rem;
			white-space: pre-wrap;
		}
		pre {
			background-color: #fff;
			border: 1px solid #ddd;
			padding: 1rem;
			max-height: 70vh;
			overflow: auto;
			white-space: pre;
			font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
			tab-size: 3;
			-moz-tab-size: 3;
		}
		.hint {
			font-size: 0.9rem;
			color: #777;
			margin-bottom: 0.5rem;
		}
	</style>
</head>
<body>
	<h1>Bifrost Tooling Dashboard</h1>
	<div class="subtitle">
		Quick insights into the current refactoring state of the framework.
	</div>

	<form method="get" action="">
		<label for="tool">Choose tool:</label>
		<select id="tool" name="tool">
			<option value="">-- select --</option>
			<option value="exceptions" <?= $selectedTool === 'exceptions' ? 'selected' : '' ?>>Exception Documentation</option>
			<option value="api" <?= $selectedTool === 'api' ? 'selected' : '' ?>>Framework API (Architecture)</option>
			<option value="structure" <?= $selectedTool === 'structure' ? 'selected' : '' ?>>Framework Structure</option>
		</select>
		<button type="submit">Run</button>
	</form>

	<?php if ($error !== null): ?>
		<div class="error">
			<?= \htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
		</div>
	<?php endif; ?>

	<?php if ($selectedTool !== null && $error === null): ?>
		<div class="hint">
			Output for tool: <strong><?= \htmlspecialchars($selectedTool, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
		</div>
		<pre><?= \htmlspecialchars($output, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></pre>
	<?php endif; ?>
</body>
</html>
<?php
#endregion