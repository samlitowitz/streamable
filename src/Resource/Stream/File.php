<?php

namespace Streamable\Resource\Stream;

use Streamable\Stream;

final class File implements Stream
{
	/** @var resource */
	private $resource;

	private function __construct($resource)
	{
		$this->setResource($resource);
	}

	public function fromFile(string $filename, string $mode): self
	{
		$h = fopen($filename, $mode);
		if ($h === false) {
			throw new \RuntimeException(
				sprintf(
					'Failed to open file with mode: filename `%s`, mode `%s`',
					$filename,
					$mode
				)
			);
		}
		return new self($h);
	}

	public function __toString()
	{
		try {
			$this->rewind();
			return $this->getContents();
		} catch (\Throwable $t) {
			return '';
		}
	}

	public function close()
	{
		fclose($this->getResource());
	}

	public function detach()
	{
	}

	public function getSize()
	{
		[
			'size' => $size,
		] = fstat($this->getResource());
		return $size;
	}

	public function tell()
	{
		$i = ftell($this->getResource());
		if ($i === false) {
			throw new \RuntimeException('Failed to get current position');
		}
		return $i;
	}

	public function eof()
	{
		return feof($this->getResource());
	}

	public function isSeekable()
	{
		[
			'seekable' => $seekable,
		] = stream_get_meta_data($this->getResource());
		return $seekable;
	}

	public function seek($offset, $whence = SEEK_SET)
	{
		$result = fseek($this->getResource(), $offset, $whence);
		if ($result === -1) {
			throw new \RuntimeException('Failed to seek');
		}
	}

	public function rewind()
	{
		$result = rewind($this->getResource());
		if ($result === false) {
			throw new \RuntimeException('Failed to rewind');
		}
	}

	public function isWritable()
	{
		[
			'mode' => $mode,
		] = stream_get_meta_data($this->getResource());
		return preg_match('/^(r\+b?|((w|a|x|c)(\+b)?))$/', $mode) === 1;
	}

	public function write($string)
	{
		$n = fwrite($this->getResource(), $string);
		if ($n === false) {
			throw new \RuntimeException('Failed to write');
		}
		if ($n !== strlen($string)) {
			throw new \RuntimeException('Failed to write');
		}
	}

	public function isReadable()
	{
		[
			'mode' => $mode,
		] = stream_get_meta_data($this->getResource());
		return preg_match('/^((r(\+b)?)|((w|a|x|c)\+b?))$/', $mode) === 1;
	}

	public function read($length)
	{
		$result = fread($this->getResource(), $length);
		if ($result === false) {
			throw new \RuntimeException('Failed to read');
		}
		return $result;
	}

	public function getContents()
	{
		$result = stream_get_contents($this->getResource());
		if ($result === false) {
			throw new \RuntimeException('Failed to get contents');
		}
		return $result;
	}

	public function getMetadata($key = null)
	{
		return stream_get_meta_data($this->getResource());
	}

	private function getResource(): resource
	{
		return $this->resource;
	}

	private function setResource(resource $resource): void
	{
		$this->resource = $resource;
	}
}
