<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\tools;

use de\bifroststormengine\support\filesystem\FileSystemExplorer;
use de\bifroststormengine\support\filesystem\Options\FileSearchOptions;
#endregion

/**
 * Generates textual descriptions of all classes/interfaces/enums
 * in a given directory tree (public API view).
 */
final class FrameworkArchitectureGenerator
{
	#region constructor
	public function __construct() {}
	#endregion

	#region public methods
	/**
	 * Exports all classes/interfaces/enums from a directory.
	 *
	 * @param string   $baseDir     Base directory to scan.
	 * @param string[] $excludeDirs List of absolute directory paths that should be excluded.
	 *
	 * @return string[] Each element is a "block" describing a single class/interface/enum.
	 */
	public function generate(string $baseDir, array $excludeDirs = []): array
	{
		$baseDirReal = \realpath($baseDir);

		if ($baseDirReal === false || !\is_dir($baseDirReal))
		{
			return [];
		}

		$options = (new FileSearchOptions())
			->withRecursive(true)
			->withExtensions(['php']);

		$allFiles = FileSystemExplorer::scanWithOptions($baseDirReal, $options);

		$classBlocks = [];

		foreach ($allFiles as $filePath)
		{
			if ($this->isExcluded($filePath, $excludeDirs))
			{
				continue;
			}

			$code = @\file_get_contents($filePath);
			if ($code === false)
			{
				if (\defined('STDERR'))
				{
					\fwrite(STDERR, "Warning: Could not read file: {$filePath}\n");
				}
				continue;
			}

			$classBlocks = \array_merge(
				$classBlocks,
				$this->extractClassesFromFile($code)
			);
		}
		return $classBlocks;
	}
	#endregion

	#region private methods
	/**
	 * Checks if a file is inside one of the excluded directories.
	 *
	 * @param string   $filePath
	 * @param string[] $excludeDirs
	 */
	private function isExcluded(string $filePath, array $excludeDirs): bool
	{
		foreach ($excludeDirs as $excludeDir)
		{
			$prefix = \rtrim($excludeDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			if ($this->str_starts_with($filePath, $prefix))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Extracts all class/interface/enum definitions from a single PHP file content.
	 *
	 * @param string $code
	 * @return string[]
	 */
	private function extractClassesFromFile(string $code): array
	{
		$results = [];

		$namespace = $this->extractNamespace($code);
		$uses      = $this->extractUses($code);

		// Match optional docblock + optional modifier + kind + name + header (extends/implements) + opening brace
		$pattern = '/(\/\*\*.*?\*\/\s*)?'                  // (1) optional docblock
			. '((?:final|abstract)\s+)?'                   // (2) optional modifier
			. '(class|interface|enum)\s+'                  // (3) kind
			. '([A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)' // (4) name
			. '([^\\{]*)\{'                                // (5) header (extends/implements etc.)
			. '/s';

		if (!\preg_match_all($pattern, $code, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE))
		{
			return [];
		}

		/** @var array<int, array<int, array{0: string, 1: int}>> $matches */
		foreach ($matches as $match) {
			$docblockRaw = $match[1][0] ?? '';
			$modifier    = \trim($match[2][0] ?? '');
			$kind        = $match[3][0]; // class|interface|enum
			$name        = $match[4][0];
			$header      = $match[5][0];

			$classStartPos = \strpos($code, '{', $match[0][1]);
			if ($classStartPos === false)
			{
				continue;
			}

			$classBody = $this->extractClassBody($code, $classStartPos);

			$extends     = $this->extractExtends($header);
			$implements  = $this->extractImplements($header);
			$isInternal  = $this->detectInternal($docblockRaw, $namespace);
			$description = $this->extractDocblockDescription($docblockRaw);

			$constructor      = $this->extractConstructorSignature($classBody);
			$publicMethods    = $this->extractPublicMethods($classBody);
			$publicConstants  = $this->extractPublicConstants($classBody);
			$publicProperties = $this->extractPublicProperties($classBody);

			$enumValues = [];
			if ($kind === 'enum')
			{
				$enumValues = $this->extractEnumValues($classBody);
			}

			$blockLines   = [];
			$blockLines[] = 'CLASS';
			$blockLines[] = "    Name: {$name}";
			$blockLines[] = "    Namespace: " . ($namespace !== '' ? $namespace : '(none)');

			$typeLine = $kind;
			if ($modifier !== '')
			{
				$typeLine .= " ({$modifier})";
			}
			$blockLines[] = "    Type: {$typeLine}";
			$blockLines[] = "    Visibility: " . ($isInternal ? 'internal (@internal)' : 'public');
			$blockLines[] = '    Implements: ' . (empty($implements) ? '—' : \implode(', ', $implements));
			$blockLines[] = '    Extends: ' . ($extends ?? '—');

			$blockLines[] = '';
			$blockLines[] = 'ANNOTATIONS';
			if ($isInternal)
			{
				$blockLines[] = '    @internal';
			}
			else
			{
				$blockLines[] = '    (none)';
			}

			$blockLines[] = '';
			$blockLines[] = 'DESCRIPTION';
			if ($description !== '')
			{
				$blockLines[] = '    ' . $description;
			}
			else
			{
				$blockLines[] = '    (no description; fill manually if needed)';
			}

			$blockLines[] = '';
			$blockLines[] = 'PUBLIC CONSTRUCTOR';
			if ($constructor !== null)
			{
				$blockLines[] = '    ' . $constructor;
			}
			else
			{
				$blockLines[] = '    (none)';
			}

			$blockLines[] = '';
			$blockLines[] = 'PUBLIC METHODS';
			if (!empty($publicMethods))
			{
				foreach ($publicMethods as $methodSig)
				{
					$blockLines[] = '    ' . $methodSig;
				}
			}
			else
			{
				$blockLines[] = '    (none)';
			}

			$blockLines[] = '';
			$blockLines[] = 'PUBLIC CONSTANTS';
			if (!empty($publicConstants))
			{
				foreach ($publicConstants as $constSig)
				{
					$blockLines[] = '    ' . $constSig;
				}
			}
			else
			{
				$blockLines[] = '    (none)';
			}

			$blockLines[] = '';
			$blockLines[] = 'ENUM VALUES';
			if ($kind === 'enum' && !empty($enumValues))
			{
				foreach ($enumValues as $enumVal)
				{
					$blockLines[] = '    ' . $enumVal;
				}
			}
			elseif ($kind === 'enum')
			{
				$blockLines[] = '    (none)';
			}
			else
			{
				$blockLines[] = '    (not applicable)';
			}

			$blockLines[] = '';
			$blockLines[] = 'PUBLIC PROPERTIES';
			if (!empty($publicProperties))
			{
				foreach ($publicProperties as $propSig)
				{
					$blockLines[] = '    ' . $propSig;
				}
			}
			else
			{
				$blockLines[] = '    (none)';
			}

			$blockLines[] = '';
			$blockLines[] = 'USES / DEPENDENCIES';
			if (!empty($uses))
			{
				foreach ($uses as $use)
				{
					$blockLines[] = '    ' . $use;
				}
			}
			else
			{
				$blockLines[] = '    (none)';
			}

			$blockLines[] = '';
			$blockLines[] = 'INTERNAL IMPLEMENTATION DETAILS';
			$blockLines[] = '    (ignored)';

			$results[] = \implode("\n", $blockLines);
		}
		return $results;
	}

	private function extractNamespace(string $code): string
	{
		if (\preg_match('/namespace\s+([^;]+);/m', $code, $m))
		{
			return \trim($m[1]);
		}
		return '';
	}

	/**
	 * @return string[]
	 */
	private function extractUses(string $code): array
	{
		$uses = [];
		if (\preg_match_all('/^use\s+([^;]+);/m', $code, $m))
		{
			foreach ($m[1] as $use)
			{
				$uses[] = \trim($use);
			}
		}
		return $uses;
	}

	private function extractClassBody(string $code, int $classStartPos): string
	{
		$length    = \strlen($code);
		$level     = 0;
		$bodyStart = $classStartPos + 1;

		for ($i = $classStartPos; $i < $length; $i++)
		{
			$char = $code[$i];
			if ($char === '{')
			{
				$level++;
			}
			elseif ($char === '}')
			{
				$level--;
				if ($level === 0)
				{
					return \substr($code, $bodyStart, $i - $bodyStart);
				}
			}
		}
		return \substr($code, $bodyStart);
	}

	private function extractExtends(string $header): ?string
	{
		if (\preg_match('/extends\s+([^\s{]+)/', $header, $m))
		{
			return \trim($m[1]);
		}
		return null;
	}

	/**
	 * @return string[]
	 */
	private function extractImplements(string $header): array
	{
		if (\preg_match('/implements\s+([^{]+)/', $header, $m))
		{
			$list = \trim($m[1]);
			if ($list === '')
			{
				return [];
			}

			$parts = \array_map('trim', \explode(',', $list));

			return \array_filter($parts, static fn(string $p): bool => $p !== '');
		}
		return [];
	}

	private function detectInternal(string $docblock, string $namespace): bool
	{
		if (\stripos($docblock, '@internal') !== false)
		{
			return true;
		}

		if (\stripos($namespace, '\\Internal\\') !== false || $this->str_ends_with($namespace, '\\Internal'))
		{
			return true;
		}
		return false;
	}

	private function extractDocblockDescription(string $docblock): string
	{
		if ($docblock === '')
		{
			return '';
		}

		$docblock = \preg_replace('/^\/\*\*|\*\/$/', '', $docblock);
		$lines    = \preg_split('/\R/', (string)$docblock);
		if ($lines === false)
		{
			return '';
		}

		foreach ($lines as $line)
		{
			$line = \trim(\ltrim($line, "* \t"));
			if ($line === '' || $this->str_starts_with($line, '@'))
			{
				continue;
			}
			return $line;
		}
		return '';
	}

	private function extractConstructorSignature(string $classBody): ?string
	{
		$pattern = '/public\s+function\s+__construct\s*\(([^)]*)\)\s*(?::\s*([^{\s]+))?/m';
		if (\preg_match($pattern, $classBody, $m))
		{
			$params = \trim($m[1]);
			$params = $this->normalizeSignatureWhitespace($params);
			$sig    = '__construct(' . $params . ')';
			if (!empty($m[2]))
			{
				$sig .= ': ' . \trim($m[2]);
			}
			return $sig;
		}
		return null;
	}

	/**
	 * @return string[]
	 */
	private function extractPublicMethods(string $classBody): array
	{
		$methods = [];
		$pattern = '/public\s+function\s+([A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*)\s*\(([^)]*)\)\s*(?::\s*([^{\s]+))?/m';

		if (\preg_match_all($pattern, $classBody, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $m)
			{
				$name = $m[1];
				if ($name === '__construct')
				{
					continue;
				}
				$params     = $this->normalizeSignatureWhitespace($m[2]);
				$returnType = isset($m[3]) ? \trim($m[3]) : '';
				$sig        = $name . '(' . $params . ')';
				if ($returnType !== '')
				{
					$sig .= ': ' . $returnType;
				}
				$methods[] = $sig;
			}
		}
		return $methods;
	}

	/**
	 * @return string[]
	 */
	private function extractPublicConstants(string $classBody): array
	{
		$constants = [];
		$pattern   = '/public\s+const\s+([A-Z0-9_]+)\s*=\s*([^;]+);/m';

		if (\preg_match_all($pattern, $classBody, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $m)
			{
				$name   = $m[1];
				$value  = \trim($m[2]);
				$constants[] = $name . ' = ' . $value;
			}
		}
		return $constants;
	}

	/**
	 * @return string[]
	 */
	private function extractEnumValues(string $classBody): array
	{
		$values  = [];
		$pattern = '/\bcase\s+([A-Z0-9_]+)\b/m';

		if (\preg_match_all($pattern, $classBody, $matches))
		{
			foreach ($matches[1] as $name)
			{
				$values[] = $name;
			}
		}
		return $values;
	}

	/**
	 * @return string[]
	 */
	private function extractPublicProperties(string $classBody): array
	{
		$properties = [];
		$pattern    = '/public\s+(readonly\s+)?([\?\|\<\>\\\\A-Za-z0-9_\x80-\xff\[\]]+\s+)?\$([A-Za-z0-9_\x80-\xff]+)/m';

		if (\preg_match_all($pattern, $classBody, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $m)
			{
				$type = \trim($m[2] ?? '');
				$name = $m[3];

				if ($type !== '')
				{
					$properties[] = '$' . $name . ': ' . $type;
				}
				else
				{
					$properties[] = '$' . $name;
				}
			}
		}
		return $properties;
	}

	private function normalizeSignatureWhitespace(string $params): string
	{
		$params = \trim($params);
		if ($params === '')
		{
			return '';
		}

		$parts      = \explode(',', $params);
		$normalized = [];

		foreach ($parts as $part)
		{
			$p            = \preg_replace('/\s+/', ' ', \trim($part));
			$normalized[] = $p;
		}
		return \implode(', ', $normalized);
	}

	private function str_starts_with(string $haystack, string $needle): bool
	{
		return $needle !== '' && \strncmp($haystack, $needle, \strlen($needle)) === 0;
	}

	private function str_ends_with(string $haystack, string $needle): bool
	{
		if ($needle === '')
		{
			return true;
		}
		return \substr($haystack, -\strlen($needle)) === $needle;
	}
	#endregion
}