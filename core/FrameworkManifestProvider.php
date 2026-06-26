<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core;

use de\bifroststormengine\core\DTO\FrameworkManifest;
#endregion

class FrameworkManifestProvider
{
	#region private constants
	private const MANIFEST_PATH = __DIR__ . '/../../framework.manifest.json';
	#endregion

	#region public static methods
	public static function load(): FrameworkManifest
	{
		return self::loadFromPath(self::MANIFEST_PATH);
	}

	public static function loadFromPath(string $path): FrameworkManifest
	{
		if (!\file_exists($path))
		{
			return new FrameworkManifest();
		}

		$content = \file_get_contents($path);
		if ($content === false)
		{
			return new FrameworkManifest();
		}

		$data = \json_decode($content, true);
		if (!\is_array($data) || \json_last_error() !== JSON_ERROR_NONE)
		{
			return new FrameworkManifest();
		}

		return self::fromArray($data);
	}
	#endregion

	#region private static methods
	private static function fromArray(array $data): FrameworkManifest
	{
		$manifest = new FrameworkManifest();

		$manifest->name           = $data['name']           ?? $manifest->name;
		$manifest->version        = $data['version']        ?? $manifest->version;
		$manifest->phpVersion     = $data['phpVersion']     ?? $manifest->phpVersion;
		$manifest->buildTimestamp = $data['buildTimestamp'] ?? $manifest->buildTimestamp;
		$manifest->gitCommit      = $data['gitCommit']      ?? null;
		$manifest->modules        = $data['modules']        ?? [];

		return $manifest;
	}
	#endregion
}