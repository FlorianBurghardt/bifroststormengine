<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Enum;
#endregion

enum ContentType: string
{
	#region cases
	case JSON            = 'application/json';
	case XML             = 'application/xml';
	case FORM_URLENCODED = 'application/x-www-form-urlencoded';
	case TEXT            = 'text/plain';
	case HTML            = 'text/html';
	case CSV             = 'text/csv';
	case BINARY          = 'application/octet-stream';
	#endregion

	#region public methods
	public function getExtension(): string
	{
		return match ($this)
		{
			self::JSON            => 'json',
			self::XML             => 'xml',
			self::FORM_URLENCODED => 'txt',
			self::TEXT            => 'txt',
			self::HTML            => 'html',
			self::CSV             => 'csv',
			self::BINARY          => 'bin',
		};
	}

	public function getLabel(): string
	{
		return match ($this)
		{
			self::JSON            => 'JSON',
			self::XML             => 'XML',
			self::FORM_URLENCODED => 'Form Data',
			self::TEXT            => 'Plain Text',
			self::HTML            => 'HTML',
			self::CSV             => 'CSV',
			self::BINARY          => 'Binary Stream',
		};
	}

	public function isJson(): bool
	{
		return $this === self::JSON;
	}

	public function isTextBased(): bool
	{
		return match ($this)
		{
			self::JSON,
			self::XML,
			self::FORM_URLENCODED,
			self::TEXT,
			self::HTML,
			self::CSV => true,
			self::BINARY => false,
		};
	}
	#endregion
}