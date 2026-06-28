<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\support\security;

use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\support\security\Enum\CryptType;
use de\bifroststormengine\support\security\KeyGenerator;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class KeyGeneratorTest extends TestKernel
{
	#region public tests
	public function testGenerateGuidProducesHexStringWithCorrectLength(): void
	{
		$length = 32;
		$guid   = KeyGenerator::generateGuid($length);

		$this->assertEquals($length, \strlen($guid), 'GUID length does not match expected length.');
		$this->assertEquals(
			1,
			\preg_match('/^[0-9a-f]+$/', $guid),
			'GUID must contain only lowercase hexadecimal characters.'
		);
	}

	public function testGenerateKeyProducesUrlSafeBase64StringWithCorrectLength(): void
	{
		$length = 40;
		$key    = KeyGenerator::generateKey($length);

		$this->assertEquals($length, \strlen($key), 'Key length does not match expected length.');
		$this->assertEquals(
			1,
			\preg_match('/^[A-Za-z0-9\-_]+$/', $key),
			'Key must be URL-safe Base64 (A-Z, a-z, 0-9, "-", "_").'
		);
	}

	public function testGenerateKeyAndEncryptReturnsExpectedStructure(): void
	{
		$cryptType = CryptType::SHA256;
		$result    = KeyGenerator::generateKeyAndEncrypt(32, $cryptType);

		$this->assertTrue(isset($result->key), 'Result must contain a "key" property.');
		$this->assertTrue(isset($result->cryptType), 'Result must contain a "cryptType" property.');
		$this->assertTrue(isset($result->encrypted), 'Result must contain an "encrypted" property.');

		$this->assertNotNull($result->key, 'Generated key must not be null.');
		$this->assertEquals($cryptType->name, $result->cryptType, 'cryptType must match the enum name.');
		$this->assertNotNull($result->encrypted, 'Encrypted value must not be null for hash-based algorithms.');
		$this->assertNotEquals('', $result->encrypted, 'Encrypted value must not be empty.');
	}

	public function testGeneratePasswordWithDefaultOptions(): void
	{
		$length   = 16;
		$password = KeyGenerator::generatePassword($length);

		$this->assertEquals($length, \strlen($password), 'Password length does not match expected length.');

		$allowedChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
					  . 'abcdefghijklmnopqrstuvwxyz'
					  . '0123456789'
					  . '!@#$%^&*()-_=+[]{}|;:,.<>?';

		$this->assertEquals(
			1,
			\preg_match('/^[' . \preg_quote($allowedChars, '/') . ']+$/', $password),
			'Password contains characters outside of the allowed default sets.'
		);
	}

	public function testGeneratePasswordThrowsFrameworkExceptionWhenNoCharacterSetEnabled(): void
	{
		try {
			KeyGenerator::generatePassword(
				length: 12,
				useUppercase: false,
				useLowercase: false,
				useNumbers: false,
				useSymbols: false
			);

			$this->fail('Expected FrameworkException was not thrown.');
		} catch (FrameworkException $e)
		{
			$this->assertEquals(HTTPExceptionType::CONFLICT, $e->getType(), 'HTTPExceptionType must be CONFLICT.');
			$this->assertEquals(11000, $e->getInnerCode(), 'Inner code must match legacy value 11000.');
		}
	}
	#endregion
}