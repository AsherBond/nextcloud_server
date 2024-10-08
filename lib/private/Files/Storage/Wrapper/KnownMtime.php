<?php

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OC\Files\Storage\Wrapper;

use OCP\Cache\CappedMemoryCache;
use OCP\Files\Storage\IStorage;
use Psr\Clock\ClockInterface;

/**
 * Wrapper that overwrites the mtime return by stat/getMetaData if the returned value
 * is lower than when we last modified the file.
 *
 * This is useful because some storage servers can return an outdated mtime right after writes
 */
class KnownMtime extends Wrapper {
	private CappedMemoryCache $knowMtimes;
	private ClockInterface $clock;

	public function __construct($arguments) {
		parent::__construct($arguments);
		$this->knowMtimes = new CappedMemoryCache();
		$this->clock = $arguments['clock'];
	}

	public function file_put_contents($path, $data): int|float|false {
		$result = parent::file_put_contents($path, $data);
		if ($result) {
			$now = $this->clock->now()->getTimestamp();
			$this->knowMtimes->set($path, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function stat($path): array|false {
		$stat = parent::stat($path);
		if ($stat) {
			$this->applyKnownMtime($path, $stat);
		}
		return $stat;
	}

	public function getMetaData($path): ?array {
		$stat = parent::getMetaData($path);
		if ($stat) {
			$this->applyKnownMtime($path, $stat);
		}
		return $stat;
	}

	private function applyKnownMtime(string $path, array &$stat): void {
		if (isset($stat['mtime'])) {
			$knownMtime = $this->knowMtimes->get($path) ?? 0;
			$stat['mtime'] = max($stat['mtime'], $knownMtime);
		}
	}

	public function filemtime($path): int|false {
		$knownMtime = $this->knowMtimes->get($path) ?? 0;
		return max(parent::filemtime($path), $knownMtime);
	}

	public function mkdir($path): bool {
		$result = parent::mkdir($path);
		if ($result) {
			$this->knowMtimes->set($path, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function rmdir($path): bool {
		$result = parent::rmdir($path);
		if ($result) {
			$this->knowMtimes->set($path, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function unlink($path): bool {
		$result = parent::unlink($path);
		if ($result) {
			$this->knowMtimes->set($path, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function rename($source, $target): bool {
		$result = parent::rename($source, $target);
		if ($result) {
			$this->knowMtimes->set($target, $this->clock->now()->getTimestamp());
			$this->knowMtimes->set($source, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function copy($source, $target): bool {
		$result = parent::copy($source, $target);
		if ($result) {
			$this->knowMtimes->set($target, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function fopen($path, $mode) {
		$result = parent::fopen($path, $mode);
		if ($result && $mode === 'w') {
			$this->knowMtimes->set($path, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function touch($path, $mtime = null): bool {
		$result = parent::touch($path, $mtime);
		if ($result) {
			$this->knowMtimes->set($path, $mtime ?? $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function copyFromStorage(IStorage $sourceStorage, $sourceInternalPath, $targetInternalPath): bool {
		$result = parent::copyFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath);
		if ($result) {
			$this->knowMtimes->set($targetInternalPath, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function moveFromStorage(IStorage $sourceStorage, $sourceInternalPath, $targetInternalPath): bool {
		$result = parent::moveFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath);
		if ($result) {
			$this->knowMtimes->set($targetInternalPath, $this->clock->now()->getTimestamp());
		}
		return $result;
	}

	public function writeStream(string $path, $stream, ?int $size = null): int {
		$result = parent::writeStream($path, $stream, $size);
		if ($result) {
			$this->knowMtimes->set($path, $this->clock->now()->getTimestamp());
		}
		return $result;
	}
}
