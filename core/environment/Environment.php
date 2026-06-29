<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\environment;
#endregion

enum Environment: string
{
	case DEV  = 'dev';
	case TEST = 'test';
	case PROD = 'prod';
}