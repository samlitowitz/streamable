<?php

namespace Streamable\Tests;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Streamable\String_;

final class StringTest extends TestCase
{
	public function testToStringDetachedSucceeds(): void
	{
		$stream = new String_(\uniqid());
		$stream->detach();
		$this->assertEmpty($stream->__toString());
	}

	public function testToStringSucceeds(): void
	{
		$expected = \uniqid();
		$stream = new String_($expected);
		$this->assertEquals($expected, $stream->__toString());
		$this->assertTrue($stream->eof());
	}

	public function testDetachSucceeds(): void
	{
		$stream = new String_();
		$this->assertNull($stream->detach());
	}

	public function testGetSizeDetachedFails(): void
	{
		$stream = new String_();
		$stream->detach();
		$this->assertNull($stream->getSize());
	}

	public function testGetSizeSucceeds(): void
	{
		$input = \uniqid();
		$stream = new String_($input);
		$this->assertEquals(strlen($input), $stream->getSize());
	}

	public function testTellDetachedFails(): void
	{
		$stream = new String_();
		$stream->detach();

		$this->expectException(RuntimeException::class);
		$stream->tell();
	}

	public function testTellSucceeds(): void
	{
		$stream = new String_();
		$this->assertEquals(0, $stream->tell());
	}

	public function testEOFDetachedFails(): void
	{
		$stream = new String_();
		$stream->detach();

		$this->expectException(RuntimeException::class);
		$stream->eof();
	}

	public function testEOFEmptyStringSucceeds(): void
	{
		$stream = new String_();
		$this->assertTrue($stream->eof());
	}

	public function testEOFDefaultPostSucceeds(): void
	{
		$stream = new String_(\uniqid());
		$this->assertFalse($stream->eof());
	}

	public function testSeekDetachedFails(): void
	{
		$stream = new String_();
		$stream->detach();

		$this->expectException(RuntimeException::class);
		$stream->seek(0);
	}

	public function testSeekUnseekableFails(): void
	{
		$stream = new String_();
		$stream->setIsSeekable(false);

		$this->expectException(RuntimeException::class);
		$stream->seek(0);
	}

	/**
	 * @dataProvider seekOutOfBoundsProvider
	 */
	public function testSeekOutOfBounds(string $data, int $offset, int $whence): void
	{
		$stream = new String_($data);

		$this->expectException(OutOfBoundsException::class);
		$stream->seek($offset, $whence);
	}

	public function seekOutOfBoundsProvider(): array
	{
		return [
			'seek set past end of stream' => [
				'',
				10,
				SEEK_SET,
			],
			'seek set before start of stream' => [
				'',
				-10,
				SEEK_SET,
			],
			'seek cur past end of stream' => [
				'',
				10,
				SEEK_CUR,
			],
			'seek cur before start of stream' => [
				'',
				-10,
				SEEK_CUR,
			],
			'seek end past end of stream' => [
				'',
				-10,
				SEEK_END,
			],
			'seek end before start of stream' => [
				'',
				10,
				SEEK_END,
			],
		];
	}

	/**
	 * @dataProvider seekSuccessProvider
	 */
	public function testSeekSuccess(
		string $data,
		int $offset,
		int $whence,
		int $seekSetPos,
		int $expectedPos
	): void {
		$stream = new String_($data);

		$stream->seek($seekSetPos);
		$stream->seek($offset, $whence);
		$this->assertEquals($expectedPos, $stream->tell());
	}

	public function seekSuccessProvider(): array
	{
		return [
			'seek set positive' => [
				'123456',
				1,
				SEEK_SET,
				0,
				1,
			],
			'seek set zero' => [
				'123456',
				0,
				SEEK_SET,
				0,
				0,
			],
			'seek cur positive' => [
				'123456',
				1,
				SEEK_CUR,
				0,
				1,
			],
			'seek cur negative' => [
				'123456',
				-1,
				SEEK_CUR,
				1,
				0,
			],
			'seek cur zero' => [
				'123456',
				0,
				SEEK_CUR,
				0,
				0,
			],
			'seek end negative' => [
				'123456',
				-1,
				SEEK_END,
				0,
				4,
			],
			'seek end zero' => [
				'123456',
				0,
				SEEK_END,
				0,
				5,
			],
		];
	}

	public function testRewindDetachedFails(): void
	{
		$stream = new String_();
		$stream->detach();

		$this->expectException(RuntimeException::class);
		$stream->rewind();
	}

	public function testRewindUnseekableFails(): void
	{
		$stream = new String_();
		$stream->setIsSeekable(false);

		$this->expectException(RuntimeException::class);
		$stream->rewind();
	}

	public function testRewindSuccess(): void
	{
		$stream = new String_();
		$stream->rewind();
		$this->assertEquals(0, $stream->tell());
	}

	public function testWriteDetachedFails(): void
	{
		$stream = new String_();
		$stream->detach();

		$this->expectException(RuntimeException::class);
		$stream->write(\uniqid());
	}

	public function testWriteUnwritableFails(): void
	{
		$stream = new String_();
		$stream->setIsWritable(false);

		$this->expectException(RuntimeException::class);
		$stream->write(\uniqid());
	}

	/**
	 * @dataProvider writeSuccessProvider
	 */
	public function testWriteSuccess(
		string $initialData,
		int $initialPos,
		string $input,
		string $expected
	): void
	{
		$stream = new String_($initialData);
		$stream->seek($initialPos);

		$n = $stream->write($input);

		$this->assertEquals(strlen($input), $n);
		$this->assertEquals($stream->__toString(), $expected);
	}

	public function writeSuccessProvider(): array
	{
		return [
			'write to empty stream' => [
				'',
				0,
				'123456',
				'123456'
			],
			'write to end of non-empty stream' => [
				'123',
				2,
				'456',
				'123456',
			],
			'write to middle of non-empty stream' => [
				'1235',
				2,
				'456',
				'123456',
			]
		];
	}

	public function testReadDetachedFails(): void
	{
		$stream = new String_();
		$stream->detach();

		$this->expectException(RuntimeException::class);
		$stream->read(0);
	}

	public function testReadUnreadableFails(): void
	{
		$stream = new String_();
		$stream->setIsReadable(false);

		$this->expectException(RuntimeException::class);
		$stream->read(0);
	}

	/**
	 * @dataProvider readSuccessProvider
	 */
	public function testReadSuccess(
		string $initialData,
		int $initialPos,
		int $input,
		string $expected
	): void
	{
		$stream = new String_($initialData);
		$stream->seek($initialPos);

		$output = $stream->read($input);
		$this->assertEquals($output, $expected);
	}

	public function readSuccessProvider(): array
	{
		return [
			'read nothing from empty stream' => [
				'',
				0,
				0,
				'',
			],
			'read from empty stream' => [
				'',
				0,
				10,
				'',
			],
			'read from EOF of non-empty stream' => [
				'123',
				2,
				3,
				'',
			],
			'read from middle of non-empty stream' => [
				'123',
				1,
				2,
				'23',
			]
		];
	}

	public function testGetContentsDetachedFails(): void
	{
		$stream = new String_();
		$stream->detach();

		$this->expectException(RuntimeException::class);
		$stream->getContents();
	}

	public function testGetContentsUnreadableFails(): void
	{
		$stream = new String_();
		$stream->setIsReadable(false);

		$this->expectException(RuntimeException::class);
		$stream->getContents();
	}

	public function testGetContentsEOFFails(): void
	{
		$stream = new String_();

		$this->expectException(RuntimeException::class);
		$stream->getContents();
	}

	public function testGetContentsSucceeds(): void
	{
		$expected = \uniqid();
		$stream = new String_($expected);

		$output = $stream->getContents();

		$this->assertEquals($expected, $output);
		$this->assertTrue($stream->eof());
	}
}
