<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\tools;
#endregion

/**
 * Beauty is a class for development, debugging and testing to print different types of information to the screen
 *
 * Attention:
 * Only for development and debugging purposes.
 * No external dependencies, no coupling to Logger or HTTP
 */
final class Beauty
{
	#region properties
	private static bool $cssIncluded = false;
	#endregion

	#region constructor
	private function __construct() {} // static-only class
	#endregion

	#region public static methods
	public static function print(mixed $input, bool $stop = false, string $title = ''): void
	{
		self::injectCssIfNeeded();

		echo "<div class='beauty'>";
		echo "<b class='beauty-title'>Beauty Printer</b><br/>";

		if ($title !== '')
		{
			echo "Title: <span class='beauty-title'>{$title}</span><br/>";
		}

		self::renderValue($input);

		echo "</div>";

		if ($stop)
		{
			exit;
		}
	}

	public static function debug(mixed $input, bool $stop = false, string $title = ''): void
	{
		self::injectCssIfNeeded();

		echo "<pre class='beauty'>";
		echo "<b class='beauty-title'>Beauty Debugger</b><br/>";

		if ($title !== '')
		{
			echo "Title: <span class='beauty-title'>{$title}</span><br/>";
		}

		$type = self::detectType($input);

		echo "Type: <span class='beauty-type'>{$type}</span><br/>";
		echo "<div class='beauty-debug'>";
		\print_r($input);
		echo "</div>";

		echo "</pre>";

		if ($stop)
		{
			exit;
		}
	}

	/**
	 * Small, neutral variant of dump(), but HTML formatted.
	 */
	public static function dump(mixed $value, string $title = ''): void
	{
		self::print($value, false, $title);
	}

	public static function detectType(mixed $value): string
	{
		return match (true)
		{
			$value === null       => 'null',
			\is_bool($value)      => 'boolean',
			\is_int($value)       => 'integer',
			\is_float($value)     => 'float',
			\is_array($value)     => 'array',
			\is_object($value)    => 'object',
			\is_resource($value)  => 'resource',
			\is_string($value)    => 'string',
			default               => 'unknown',
		};
	}

	/**
	 * @internal Only for Tests/Debugging
	 */
	public static function resetForTests(): void
	{
		self::$cssIncluded = false;
	}
	#endregion

	#region private static methods
	private static function renderValue(mixed $value, ?string $key = null): void
	{
		$type = self::detectType($value);

		$keyHtml = $key !== null
			? "<span class='beauty-key'>[{$key}]</span>"
			: '';

		echo "<div class='beauty-space'><span class='beauty-type'>{$type}</span>{$keyHtml} ";

		if (\is_array($value))
		{
			echo "<div class='beauty-marker'>[";
			foreach ($value as $k => $v)
			{
				self::renderValue($v, (string)$k);
			}
			echo "]</div></div>";
			return;
		}

		if (\is_object($value))
		{
			echo "<div class='beauty-marker'>{";
			foreach (\get_object_vars($value) as $k => $v)
			{
				self::renderValue($v, (string)$k);
			}
			echo "}</div></div>";
			return;
		}

		$formatted = match ($type)
		{
			'null'     => 'null',
			'boolean'  => $value ? 'true' : 'false',
			'string'   => \htmlspecialchars($value),
			default    => (string)$value,
		};

		echo "<span class='beauty-value'>{$formatted}</span>";
		echo "</div>";
	}
	#endregion

	#region css styler
	private static function injectCssIfNeeded(): void
	{
		if (self::$cssIncluded)
		{
			return;
		}
		self::$cssIncluded = true;

		echo "<style>" . self::$css . "</style>";
	}

	private static string $css = "
		.beauty { background-color: rgb(34, 34, 34); color: rgb(118, 238, 0); border: 3px solid rgb(102, 217, 239); border-radius: 0.5em; padding: 0.5em; line-height: 1.5em; font-family: monospace; font-size: 1.2em; }
		.beauty-title { color: rgb(255, 255, 0); }
		.beauty-type { color: rgb(54, 169, 255); display: inline-block; min-width: 60px; }
		.beauty-marker { color: rgb(255, 205, 0); }
		.beauty-key { display: inline-block; color: rgb(205, 0, 170); margin-left: 0.5em; }
		.beauty-value { color: rgb(178, 50, 255); margin-left: 0.5em; }
		.beauty-debug { color: rgb(255, 136, 0); margin-left: 0.5em; }
		.beauty-space { margin-left: 1.5em; }
	";
	#endregion
}