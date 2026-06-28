<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\security\Enum;
#endregion

/**
 * Supported cryptographic algorithms.
 */
enum CryptType
{
	#region cases
	case MD5;
	case SHA1;
	case SHA256;
	case SHA512;
	case PASSWORD_HASH;
	case HMAC_SHA256;
	case AES_256_CBC;
	#endregion
}