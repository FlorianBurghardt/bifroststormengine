<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\security;

use de\bifroststormengine\support\security\Enum\CryptType;
#endregion

/**
 * Helper class for cryptographic operations (hashing, HMAC, symmetric encryption).
 */
final class CryptoVerifier
{
	#region constructor
	/**
	 * Private constructor to prevent instantiation.
	 */
	private function __construct() {}
	#endregion

	#region public static methods
	/**
	 * Verifies a given string using the given crypt type and optional key.
	 */
	public static function verify(
		string $input,
		string $encrypted,
		CryptType $cryptType,
		?string $key = null
	): bool
	{
		return match ($cryptType)
		{
			CryptType::MD5           => self::verifyMd5($input, $encrypted),
			CryptType::SHA1          => self::verifySha1($input, $encrypted),
			CryptType::SHA256        => self::verifySha256($input, $encrypted),
			CryptType::SHA512        => self::verifySha512($input, $encrypted),
			CryptType::PASSWORD_HASH => self::verifyPasswordHash($input, $encrypted),
			CryptType::HMAC_SHA256   => self::verifyHmacSha256($input, $encrypted, $key),
			CryptType::AES_256_CBC   => self::verifyAes256Cbc($input, $encrypted, $key),
		};
	}

	/**
	 * Encrypts a given string using the given crypt type and optional key.
	 *
	 * @return string|null Null if the type requires a key but none is provided.
	 */
	public static function encrypt(
		string $input,
		CryptType $type,
		?string $key = null
	): ?string
	{
		return match ($type)
		{
			CryptType::MD5           => self::encryptMd5($input),
			CryptType::SHA1          => self::encryptSha1($input),
			CryptType::SHA256        => self::encryptSha256($input),
			CryptType::SHA512        => self::encryptSha512($input),
			CryptType::PASSWORD_HASH => self::encryptPasswordHash($input),
			CryptType::HMAC_SHA256   => $key ? self::encryptHmacSha256($input, $key) : null,
			CryptType::AES_256_CBC   => $key ? self::encryptAes256Cbc($input, $key) : null,
		};
	}

	/**
	 * Decrypts a given string using the given crypt type.
	 *
	 * @return string|null The decrypted string, or null if the type is not reversible.
	 */
	public static function decrypt(
		string $input,
		CryptType $type,
		?string $key = null
	): ?string
	{
		return match ($type)
		{
			CryptType::AES_256_CBC => $key ? self::decryptAes256Cbc($input, $key) : null,
			default                => null, // Hashes cannot be reversed
		};
	}
	#endregion

	#region private static methods
	// -------------------------------------------------------------------------
	// Verify methods
	// -------------------------------------------------------------------------

	private static function verifyMd5(string $input, string $encrypted): bool
	{
		return self::encryptMd5($input) === $encrypted;
	}

	private static function verifySha1(string $input, string $encrypted): bool
	{
		return self::encryptSha1($input) === $encrypted;
	}

	private static function verifySha256(string $input, string $encrypted): bool
	{
		return self::encryptSha256($input) === $encrypted;
	}

	private static function verifySha512(string $input, string $encrypted): bool
	{
		return self::encryptSha512($input) === $encrypted;
	}

	private static function verifyPasswordHash(string $input, string $encrypted): bool
	{
		return \password_verify($input, $encrypted);
	}

	private static function verifyHmacSha256(
		string $input,
		string $encrypted,
		?string $key
	): bool
	{
		if ($key === null)
		{
			return false;
		}

		return hash_equals(
			self::encryptHmacSha256($input, $key),
			$encrypted
		);
	}

	private static function verifyAes256Cbc(
		string $input,
		string $encrypted,
		?string $key
	): bool
	{
		if ($key === null)
		{
			return false;
		}

		$decrypted = self::decryptAes256Cbc($encrypted, $key);

		if ($decrypted === null)
		{
			return false;
		}

		return \hash_equals($decrypted, $input);
	}

	// -------------------------------------------------------------------------
	// Encrypt / decrypt methods
	// -------------------------------------------------------------------------

	private static function encryptMd5(string $input): string
	{
		return \hash('md5', $input);
	}

	private static function encryptSha1(string $input): string
	{
		return \hash('sha1', $input);
	}

	private static function encryptSha256(string $input): string
	{
		return \hash('sha256', $input);
	}

	private static function encryptSha512(string $input): string
	{
		return \hash('sha512', $input);
	}

	private static function encryptPasswordHash(string $input): string
	{
		return \password_hash($input, PASSWORD_DEFAULT);
	}

	private static function encryptHmacSha256(string $input, string $key): string
	{
		return \hash_hmac('sha256', $input, $key);
	}

	/**
	 * Encrypts a given string using AES-256-CBC with the given key.
	 *
	 * A random IV is generated and prepended to the ciphertext; the result is base64 encoded.
	 */
	private static function encryptAes256Cbc(string $input, string $key): string
	{
		$iv         = \random_bytes(16);
		$ciphertext = \openssl_encrypt($input, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

		return \base64_encode($iv . $ciphertext);
	}

	/**
	 * Decrypts a given AES-256-CBC encrypted string with the given key.
	 *
	 * @return string|null The decrypted string, or null on failure.
	 */
	private static function decryptAes256Cbc(string $input, string $key): ?string
	{
		$decoded = \base64_decode($input, true);

		if ($decoded === false || \strlen($decoded) < 16)
		{
			return null;
		}

		$iv         = \substr($decoded, 0, 16);
		$ciphertext = \substr($decoded, 16);

		return \openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv) ?: null;
	}
	#endregion
}