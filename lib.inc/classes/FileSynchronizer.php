<?php
class FileSynchronizer
{
	const NEW_LINE = "\r\n";
	/**
	 * Base path
	 */
	public $basePath = '';

	/**
	 * Pooling file name
	 */
	public $fileName = 'pool.txt';

	/**
	 * Rolling name
	 */
	public $prefix = 'pool_';
	/**
	 * File extenstion
	 */
	public $extension = '.txt';
	/**
	 * Maximum file size
	 */
	private $maximumlength = 20000;
	
	/**
	 * Constructor of FileSynchronizer
	 * @param string $basePath Base path
	 * @param string $fileName File name
	 * @param string $prefix File prefix
	 * @param string $extension Extension
	 * @param int $maximumlength Maximum length
	 */
	public function __construct($basePath, $fileName, $prefix, $extension, $maximumlength)
	{
		$this->basePath = $basePath;
		if($fileName != null)
		{
			$this->fileName = $fileName;
		}
		if($prefix != null)
		{
			$this->prefix = $prefix;
		}
		if($extension != null)
		{
			$this->extension = $extension;
		}
		if($maximumlength > 0)
		{
			$this->maximumlength = $maximumlength;
		}
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
	 * @return string Absolute pooling path
	 */
	public function getPoolPath()
	{
		$poolPath = $this->basePath . "/" . $this->fileName . $this->extension;
		if(file_exists($poolPath) && filesize($poolPath) > $this->maximumlength)
		{
			$newPath = $this->basePath . "/" . $this->prefix.date('Y-m-d-H-i-s') . "-" . $this->generateNewId() . $this->extension;
			rename($poolPath, $newPath);
		}
		return $poolPath;
	}

	/**
	 * Create sync command when file with content
	 * @param string $path Absolute path to be craeted
	 * @param string $content File content
	 * @param bool $sync Flag that file creation will be synchronized or not
	 * @return bool|int Number of bytes written to file
	 */
	public function createFileWithContent($path, $content, $sync)
	{
		if($sync)
		{
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'CREATEFILE',
				'tm'=>$time,
				'path'=>$path
			));
			fwrite($fp, $syncContent.self::NEW_LINE);  
			fclose($fp);  
		}
		return file_put_contents($path, $content);
	}

	/**
	 * Create sync command when file without content
	 * @param string $path Absolute path to be craeted
	 * @param bool $sync Flag that file creation will be synchronized or not
	 * @return bool|int Number of bytes written to sync file
	 */
	public function createFile($path, $sync)
	{
		if($sync)
		{
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'CREATEFILE',
				'tm'=>$time,
				'path'=>$path
			));
			$ret = fwrite($fp, $syncContent.self::NEW_LINE);  
			fclose($fp);
			return $ret; 
		}
		return true;
	}

	/**
	 * Delete file
	 * @param string $path Absolute path to be deleted
	 * @param bool $sync Flag that file deleteion will be synchronized or not
	 * @return bool true on success or false on failure.
	 */
	public function deleteFile($path, $sync)
	{
		if($sync)
		{
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'DELETEFILE',
				'tm'=>$time,
				'path'=>$path
			));
			fwrite($fp, $syncContent.self::NEW_LINE);  
			fclose($fp);  
		}
		return @unlink($path);
	}

	/**
	 * Renames a file or directory
	 * @param string $oldPath Old name
	 * @param string $newPath New name
	 * @param bool $sync Flag that renaming file will be synchronized or not
	 * @return bool true on success or false on failure.
	 */
	public function renameFile($oldPath, $newPath, $sync)
	{
		if($sync)
		{
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'RENAMEFILE',
				'tm'=>$time,
				'path'=>$oldPath,
				'to'=>$newPath
			));
			fwrite($fp, $syncContent.self::NEW_LINE);  
			fclose($fp);  
		}
		return @rename($oldPath, $newPath);
	}

	/**
	 * Prepare directory
	 * @param string $dir2prepared Path to be pepared
	 * @param string $dirBase Base directory
	 * @param int $permission File permission
	 * @param bool $sync Flag that renaming file will be synchronized or not
	 * @return void
	 */
	public function prepareDirecory($dir2prepared, $dirBase, $permission, $sync = false)
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
				$this->createDirecory($dir2created, $permission, $sync);
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
	public function createDirecory($path, $permission, $sync)
	{
		if($sync)
		{
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'CREATEDIR',
				'tm'=>$time,
				'path'=>$path
			));
			fwrite($fp, $syncContent.self::NEW_LINE);  
			fclose($fp);  
		}
		return @mkdir($path, $permission);
	}

	/**
	 * Delete directory
	 * @param string $path Path to be deleted
	 * @param bool $sync Flag that file deletion will be synchronized or not
	 * @return bool true on success or false on failure.
	 */
	public function deleteDirecory($path, $sync)
	{
		if($sync)
		{
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'DELETEDIR',
				'tm'=>$time,
				'path'=>$path
			));
			fwrite($fp, $syncContent.self::NEW_LINE);  
			fclose($fp);  
		}
		$perms = fileperms($path);
		chmod($path, 0777);
		$ret = @rmdir($path);
		if(!$ret)
		{
			chmod($path, $perms);
		}
		return $ret;
	}

}