<?php

namespace Pico;

class PicoDatabaseSyncConfig
{
	private $applicationDir = '';
	private $baseDir = '';
	private $poolName = '';
	private $rollingPrefix = '';
	private $extension = '';
	private $maximumlength = 1000000;
	private $delimiter = '------------------------912284ba5a823ba425efba890f57a4e2c88e8369';
	

	/**
	 * Constructor of PicoDatabaseSyncConfig
	 * @param string $applicationDir Base directory of sync file
	 * @param string $baseDir Base directory of sync file
	 * @param string $poolName Pooling file name
	 * @param string $rollingPrefix Rolling prefix file name
	 * @param string $extension File extension
	 * @param int $maximumlength Maximum length of sync file
	 * @param string $delimiter Extra query delimiter
	 */
	public function __construct($applicationDir, $baseDir, $poolName, $rollingPrefix, $extension, $maximumlength, $delimiter)
	{
		$this->applicationDir = $applicationDir;
		$this->baseDir = $baseDir;
		$this->poolName = $poolName;
		$this->rollingPrefix = $rollingPrefix;
		$this->extension = $extension;
		$this->maximumlength = $maximumlength;
		$this->delimiter = $delimiter;
	}

	/**
	 * Generate 20 bytes unique ID
	 * @return string 20 bytes
	 */
	public function generateNewId()
	{
		$uuid = uniqid();
		if((strlen($uuid) % 2) == 1)
		{
			$uuid = '0'.$uuid;
		}
		$random = sprintf('%06x', mt_rand(0, 16777215));
		return sprintf('%s%s', $uuid, $random);
	}

	/**
	 * Get pooling path
	 * @return string Polling path
	 */
	public function getPoolPath()
	{
		if(!file_exists($this->baseDir))
		{
			$this->prepareDirectory($this->baseDir, $this->applicationDir, 0777);
		}
		$poolPath = $this->baseDir . "/" . $this->poolName . $this->extension;
		if(file_exists($poolPath) && filesize($poolPath) > $this->maximumlength)
		{
			$newPath = $this->baseDir . "/" . $this->rollingPrefix.date('Y-m-d-H-i-s')."-".$this->generateNewId().$this->extension;
			rename($poolPath, $newPath);
		}
		return $poolPath;
	}

	/**
	 * Append query to sync file
	 * @param string $sql Query to be synchronized
	 * @return int Number of byte written to sync file include delimiter
	 */
	public function createSync($sql)
	{
		$syncPath = $this->getPoolPath();
		$fp = fopen($syncPath, 'a');
		$l1 = fwrite($fp, $this->delimiter.\Pico\PicoConst::NEW_LINE);
		$l2 = fwrite($fp, trim($sql).";".\Pico\PicoConst::NEW_LINE);
		fclose($fp);
		return $l1 + $l2;
	}

	/**
	 * Get sync file delimiter
	 * @return string Sync file delimiter
	 */
	public function getDelimiter()
	{
		return $this->delimiter;
	}

	/**
	 * Prepare directory
	 * @param string $dir2prepared Path to be pepared
	 * @param string $dirBase Base directory
	 * @param int $permission File permission
	 * @param bool $sync Flag that renaming file will be synchronized or not
	 * @return void
	 */
	public function prepareDirectory($dir2prepared, $dirBase, $permission)
	{
		$dir = str_replace("\\", "/", $dir2prepared);
		$base = str_replace("\\", "/", $dirBase);
		$arrDir = explode("/", $dir);
		$arrBase = explode("/", $base);
		$base = implode("/", $arrBase);
		$dir2created = "";
		foreach($arrDir as $val)
		{
			$dir2created .= $val;
			if(stripos($base, $dir2created) !== 0 && !file_exists($dir2created))
			{
				$this->createDirecory($dir2created, $permission);
			}
			$dir2created .= "/";
		}
	}

	/**
	 * Create directory
	 * @param string $path Path to be created
	 * @param int $permission File permission
	 * @param bool $sync Flag that renaming file will be synchronized or not
	 * @return bool true on success or false on failure.
	 */
	public function createDirecory($path, $permission)
	{
		return @mkdir($path, $permission);
	}
}