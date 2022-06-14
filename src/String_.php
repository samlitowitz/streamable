<?php

namespace Streamable;

use OutOfBoundsException;
use RuntimeException;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

final class String_ implements Stream
{
	/** @var bool */
	private $_isDetached;
	/** @var bool */
	private $_isReadable;
	/** @var bool */
	private $_isSeekable;
	/** @var bool */
	private $_isWritable;
	/** @var int */
	private $_pos;
	/** @var string */
	private $_string;

	public function __construct(string $_string = '')
	{
		$this->setIsDetached(false);
		$this->setIsReadable(true);
		$this->setIsSeekable(true);
		$this->setIsWritable(true);
		$this->setPos(0);
		$this->setString($_string);
	}

	public function __toString()
	{
		if ($this->getIsDetached()) {
			return '';
		}
		$this->seek(0);
		return $this->read($this->getSize());
	}

	public function close()
	{
	}

	public function detach()
	{
		$this->setIsDetached(true);
		return null;
	}

	public function getSize(): ?int
	{
		if ($this->getIsDetached()) {
			return null;
		}
		return strlen($this->getString());
	}

	public function tell(): int
	{
		if ($this->getIsDetached()) {
			throw new RuntimeException('resource is detached');
		}
		return $this->getPos();
	}

	public function eof(): bool
	{
		return $this->getPos() >= strlen($this->getString()) - 1;
	}

	public function isSeekable(): bool
	{
		return $this->getIsSeekable();
	}

	public function seek($offset, $whence = SEEK_SET)
	{
		if (!$this->isSeekable()) {
			throw new RuntimeException('stream is not seekable');
		}
		switch ($whence) {
			case SEEK_SET:
				break;
			case SEEK_CUR:
				$offset = $this->getPos() + $offset;
				break;
			case SEEK_END:
				$offset = $this->getSize() - 1 + $offset;
				break;
			default:
				throw new RuntimeException('invalid whence: ' . $whence);
		}
		if ($offset > $this->getSize() - 1) {
			throw new OutOfBoundsException('cannot seek past end of stream');
		}
		if ($offset < 0) {
			throw new OutOfBoundsException('cannot seek before start of stream');
		}
		$this->setPos($offset);
	}

	public function rewind()
	{
		if (!$this->isSeekable()) {
			throw new RuntimeException('stream is not seekable');
		}
		$this->seek(0);
	}

	public function isWritable(): bool
	{
		return $this->getIsWritable();
	}

	public function write($string): int
	{
		$this->setString(
			\substr_replace($this->getString(), $string, $this->getPos() + 1)
		);
		return strlen($string);
	}

	public function isReadable(): bool
	{
		return $this->getIsReadable();
	}

	public function read($length): string
	{
		if ($this->getIsDetached()) {
			throw new RuntimeException('resource is detached');
		}
		if (!$this->isReadable()) {
			throw new RuntimeException('stream is not readable');
		}
		if ($this->eof()) {
			return '';
		}
		$length = \min($length, $this->getSize() - $this->getPos());

		$data = substr($this->getString(), $this->getPos(), $length);
		$this->setPos($this->getPos() + $length);
		return $data;
	}

	public function getContents()
	{
		if ($this->getIsDetached()) {
			throw new RuntimeException('resource is detached');
		}
		if ($this->isReadable()) {
			throw new RuntimeException('stream is not readable');
		}
		if ($this->eof()) {
			throw new OutOfBoundsException('eof');
		}
		return $this->read($this->getSize());
	}

	public function getMetadata($key = null)
	{
		return null;
	}

	private function getIsDetached(): bool
	{
		return $this->_isDetached;
	}

	private function setIsDetached(bool $isDetached): void
	{
		$this->_isDetached = $isDetached;
	}

	private function getIsReadable(): bool
	{
		return $this->_isReadable;
	}

	public function setIsReadable(bool $isReadable): void
	{
		$this->_isReadable = $isReadable;
	}

	private function getIsSeekable(): bool
	{
		return $this->_isSeekable;
	}

	public function setIsSeekable(bool $isSeekable): void
	{
		$this->_isSeekable = $isSeekable;
	}

	private function getIsWritable(): bool
	{
		return $this->_isWritable;
	}

	public function setIsWritable(bool $isWritable): void
	{
		$this->_isWritable = $isWritable;
	}

	private function getPos(): int
	{
		return $this->_pos;
	}

	private function setPos(int $pos): void
	{
		$this->_pos = $pos;
	}

	private function getString(): string
	{
		return $this->_string;
	}

	private function setString(string $string): void
	{
		$this->_string = $string;
	}
}
