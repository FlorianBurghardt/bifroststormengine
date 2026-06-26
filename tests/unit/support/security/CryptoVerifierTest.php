<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\support\security;

use de\bifroststormengine\support\security\CryptoVerifier;
use de\bifroststormengine\support\security\Enum\CryptType;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class CryptoVerifierTest extends TestKernel
{
	#region public tests
	public function testEncryptAndVerifyHashAlgorithms(): void
	{
		$input = 'my-secret-value';

		$algorithms = [
			CryptType::MD5,
			CryptType::SHA1,
			CryptType::SHA256,
			CryptType::SHA512,
		];

		foreach ($algorithms as $type)
		{
			$hash = CryptoVerifier::encrypt($input, $type);

			$this->assertNotNull($hash, \sprintf('Hash for %s must not be null.', $type->name));
			$this->assertNotEquals('', $hash, \sprintf('Hash for %s must not be empty.', $type->name));

			$this->assertTrue(
				CryptoVerifier::verify($input, $hash, $type),
				\sprintf('Verify() must return true for %s with correct input.', $type->name)
			);

			$this->assertFalse(
				CryptoVerifier::verify('wrong-value', $hash, $type),
				\sprintf('Verify() must return false for %s with incorrect input.', $type->name)
			);
		}
	}

	public function testPasswordHashEncryptAndVerify(): void
	{
		$password = 'Super$ecureP@ssw0rd';

		$hash = CryptoVerifier::encrypt($password, CryptType::PASSWORD_HASH);

		$this->assertNotNull($hash, 'Password hash must not be null.');
		$this->assertNotEquals('', $hash, 'Password hash must not be empty.');

		$this->assertTrue(
			CryptoVerifier::verify($password, $hash, CryptType::PASSWORD_HASH),
			'Verify() must return true for PASSWORD_HASH with correct password.'
		);

		$this->assertFalse(
			CryptoVerifier::verify('wrong-password', $hash, CryptType::PASSWORD_HASH),
			'Verify() must return false for PASSWORD_HASH with incorrect password.'
		);
	}

	public function testHmacSha256EncryptAndVerify(): void
	{
		$input = 'important-message';
		$key   = 'my-hmac-key';

		$hmac = CryptoVerifier::encrypt($input, CryptType::HMAC_SHA256, $key);

		$this->assertNotNull($hmac, 'HMAC must not be null.');
		$this->assertNotEquals('', $hmac, 'HMAC must not be empty.');

		$this->assertTrue(
			CryptoVerifier::verify($input, $hmac, CryptType::HMAC_SHA256, $key),
			'Verify() must return true for HMAC_SHA256 with correct key and input.'
		);

		$this->assertFalse(
			CryptoVerifier::verify($input, $hmac, CryptType::HMAC_SHA256, null),
			'Verify() must return false for HMAC_SHA256 when no key is provided.'
		);

		$this->assertFalse(
			CryptoVerifier::verify($input, $hmac, CryptType::HMAC_SHA256, 'wrong-key'),
			'Verify() must return false for HMAC_SHA256 with wrong key.'
		);
	}

	public function testAes256CbcEncryptDecryptAndVerify(): void
	{
		$input = 'sensitive-payload';
		$key   = '0123456789abcdef0123456789abcdef';

		$encrypted = CryptoVerifier::encrypt($input, CryptType::AES_256_CBC, $key);

		$this->assertNotNull($encrypted, 'AES encrypted value must not be null.');
		$this->assertNotEquals('', $encrypted, 'AES encrypted value must not be empty.');

		$decrypted = CryptoVerifier::decrypt($encrypted, CryptType::AES_256_CBC, $key);

		$this->assertNotNull($decrypted, 'AES decrypted value must not be null.');
		$this->assertEquals($input, $decrypted, 'AES decrypted value must match original input.');

		$this->assertTrue(
			CryptoVerifier::verify($input, $encrypted, CryptType::AES_256_CBC, $key),
			'Verify() must return true for AES_256_CBC with correct key and input.'
		);

		$this->assertFalse(
			CryptoVerifier::verify($input, $encrypted, CryptType::AES_256_CBC, 'wrong-key'),
			'Verify() must return false for AES_256_CBC with wrong key.'
		);
	}

	public function testDecryptReturnsNullForNonReversibleTypes(): void
	{
		$input = 'my-value';
		$hash  = CryptoVerifier::encrypt($input, CryptType::SHA256);

		$this->assertNotNull($hash, 'Hash must not be null.');

		$decrypted = CryptoVerifier::decrypt($hash, CryptType::SHA256, null);

		$this->assertNull(
			$decrypted,
			'Decrypt() must return null for non-reversible algorithms like SHA256.'
		);
	}
	#endregion
}