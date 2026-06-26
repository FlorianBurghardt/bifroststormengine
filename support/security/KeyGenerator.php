<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\security;

use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\support\security\Enum\CryptType;
use stdClass;
#endregion

/**
 * Static helper class for key and password generation.
 */
final class KeyGenerator
{
	#region constructor
	/**
	 * Private constructor to prevent instantiation.
	 */
	private function __construct() {}
	#endregion

	#region public static methods
	/**
	 * Generates a new GUID-like hexadecimal token.
	 *
	 * @param int $length Length of the hex token (default: 32).
	 */
	public static function generateGuid(int $length = 32): string
	{
		return self::generateHexToken($length);
	}

	/**
	 * Generates a new random Base64-like key.
	 *
	 * @param int $length Optional key length (default: 32).
	 */
	public static function generateKey(int $length = 32): string
	{
		return self::generateBase64Token($length);
	}

	/**
	 * Generates a random key, encrypts it and returns the result.
	 *
	 * The result object contains:
	 *  - key        (string) The generated plain key
	 *  - cryptType  (string) The name of the crypt type used
	 *  - encrypted  (string|null) The encrypted representation
	 */
	public static function generateKeyAndEncrypt(
		int $length = 32,
		CryptType $cryptType = CryptType::MD5
	): stdClass
	{
		$result = new stdClass();

		$result->key       = self::generateBase64Token($length);
		$result->cryptType = $cryptType->name;
		$result->encrypted = CryptoVerifier::encrypt($result->key, $cryptType, null);

		return $result;
	}

	/**
	 * Generates a random password based on configuration options.
	 *
	 * @param int         $length       Length of the password (default: 12)
	 * @param bool        $useUppercase Include uppercase letters
	 * @param bool        $useLowercase Include lowercase letters
	 * @param bool        $useNumbers   Include numbers
	 * @param bool        $useSymbols   Include special characters
	 * @param array|null  $customSets   Optional associative array to override default character sets:
	 *                                  [
	 *                                      'uppercase' => 'ABCDEF...',
	 *                                      'lowercase' => 'abcdef...',
	 *                                      'numbers'   => '012345...',
	 *                                      'symbols'   => '!@#$...'
	 *                                  ]
	 *
	 * @throws FrameworkException If no character set is enabled.
	 */
	public static function generatePassword(
		int $length = 12,
		bool $useUppercase = true,
		bool $useLowercase = true,
		bool $useNumbers = true,
		bool $useSymbols = true,
		?array $customSets = null
	): string
	{
		$upper   = $customSets['uppercase'] ?? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$lower   = $customSets['lowercase'] ?? 'abcdefghijklmnopqrstuvwxyz';
		$numbers = $customSets['numbers']   ?? '0123456789';
		$symbols = $customSets['symbols']   ?? '!@#$%^&*()-_=+[]{}|;:,.<>?';

		$characters = '';
		if ($useUppercase) { $characters .= $upper; }
		if ($useLowercase) { $characters .= $lower; }
		if ($useNumbers) { $characters .= $numbers; }
		if ($useSymbols) { $characters .= $symbols; }

		if ($characters === '')
		{
			throw new FrameworkException(HTTPExceptionType::CONFLICT, 11000, 'At least one character set must be enabled.');
		}

		$password  = '';
		$charArray = \str_split($characters);
		$charCount = \count($charArray);

		for ($i = 0; $i < $length; $i++) {
			$password .= $charArray[\random_int(0, $charCount - 1)];
		}
		return $password;
	}
	#endregion

	#region private static methods
	/**
	 * Generates a new random URL-safe Base64 token.
	 */
	private static function generateBase64Token(int $length = 32): string
	{
		$byteLength = (int) \ceil($length * 3 / 4);
		$base64     = \base64_encode(\random_bytes($byteLength));
		$base64     = \rtrim(\strtr($base64, '+/', '-_'), '=');

		return \substr($base64, 0, $length);
	}

	/**
	 * Generates a new random hexadecimal token.
	 *
	 * Note: For best entropy, use an even length.
	 */
	private static function generateHexToken(int $length = 32): string
	{
		return \strtolower(\bin2hex(\random_bytes((int) ($length / 2))));
	}
	#endregion
}