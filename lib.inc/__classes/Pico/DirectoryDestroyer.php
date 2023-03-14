<?php

namespace Pico;

class DirectoryDestroyer
{
	/**
	 * File synchronizer
	 *
	 * @var \Pico\FileSynchronizer
	 */
	public $fileSync = null;

	/**
	 * Constructor of DirectoryDestroyer
	 *
	 * @param \Pico\FileSynchronizer $fileSync
	 */
	public function __construct($fileSync)
	{
		$this->fileSync = $fileSync;
	}

	/**
	 * Destroy directory
	 *
	 * @param string $dir Directory
	 * @param bool $sync Flag to sync
	 * @return void
	 */
	public function destroy($dir, $sync)
	{
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir . "/" . $object) == "dir") {
						$this->destroy($dir . "/" . $object, $sync);
					} else {
						$this->fileSync->deleteFile($dir . "/" . $object, $sync);
					}
				}
			}
			reset($objects);
			$this->fileSync->deleteDirecory($dir, $sync);
		}
	}
}
