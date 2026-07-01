<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\Enum;
#endregion

enum TestExceptionType : int
{
	#region cases
	case ASSERTION_EQUALS      = 1000;
	case ASSERTION_NOT_EQUALS  = 1001;
	case ASSERTION_TRUE        = 1002;
	case ASSERTION_FALSE       = 1003;
	case ASSERTION_NULL        = 1004;
	case ASSERTION_NOT_NULL    = 1005;
	case ASSERTION_INSTANCE_OF = 1006;
	case ASSERTION_THROWS      = 1007;
	case ASSERTION_FAILED      = 1008;
	case ASSERTION_SAME        = 1009;
	case ASSERTION_COUNT       = 1010;
	#endregion
}