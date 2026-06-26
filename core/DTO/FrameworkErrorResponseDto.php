<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\DTO;
#endregion

final class FrameworkErrorResponseDto
{
	#region properties
	public FrameworkErrorDto $error;
	public FrameworkManifest $framework;
	#endregion

	#region constructor
	public function __construct(
		FrameworkErrorDto $error,
		FrameworkManifest $framework
	)
	{
		$this->error     = $error;
		$this->framework = $framework;
	}
	#endregion

	#region public methods
	public function toArray(): array
	{
		return [
			'error'     => $this->error->toArray(),
			'framework' => $this->framework->toArray(),
		];
	}
	#endregion
}