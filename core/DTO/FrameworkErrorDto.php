<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\DTO;
#endregion

final class FrameworkErrorDto
{
	#region properties
	public string $type;
	public string $message;
	public int $innerCode;
	public int $httpStatus;
	#endregion

	#region constructor
	public function __construct(
		string $type,
		string $message,
		int $innerCode,
		int $httpStatus
	)
	{
		$this->type       = $type;
		$this->message    = $message;
		$this->innerCode  = $innerCode;
		$this->httpStatus = $httpStatus;
	}
	#endregion

	#region public methods
	public function toArray(): array
	{
		return [
			'type'       => $this->type,
			'message'    => $this->message,
			'innerCode'  => $this->innerCode,
			'httpStatus' => $this->httpStatus,
		];
	}
	#endregion
}