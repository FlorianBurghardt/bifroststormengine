<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\DTO;

use de\bifroststormengine\core\Framework;
#endregion

class FrameworkManifest
{
	#region properties
	public string $name;
	public string $version;
	public string $phpVersion;
	public string $buildTimestamp;
	public ?string $gitCommit = null;
	public array $modules = [];
	#endregion

	#region constructor
	public function __construct()
	{
		$this->name           = Framework::getName();
		$this->version        = Framework::VERSION;
		$this->phpVersion     = PHP_VERSION;
		$this->buildTimestamp = \date('c');
	}
	#endregion

	#region public methods
	public function toArray(): array
	{
		return [
			'name'           => $this->name,
			'version'        => $this->version,
			'phpVersion'     => $this->phpVersion,
			'buildTimestamp' => $this->buildTimestamp,
			'gitCommit'      => $this->gitCommit,
			// 'modules' => $this->modules, // ToDo extend this later
		];
	}
	#endregion
}