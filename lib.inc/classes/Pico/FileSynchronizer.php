<?php
namespace Pico;
class FileSynchronizer
{
	const NEW_LINE = "\r\n";

	public string $applicationDir = '';

	/**
	 * Base directory
	 */
	public string $baseDir = '';

	/**
	 * Pooling file name
	 */
	public string $fileName = 'pool.txt';

	/**
	 * Rolling name
	 */
	public string $prefix = 'pool_';

	/**
	 * File extenstion
	 */
	public string $extension = '.txt';

	/**
	 * Maximum file size
	 */
	private int $maximumlength = 20000;

	private bool $useRelativePath = false;
	
	/**
	 * Constructor of FileSynchronizer
	 * @param string $applicationDir Application directory
	 * @param string $baseDir Base path
	 * @param string $fileName File name
	 * @param string $prefix File prefix
	 * @param string $extension Extension
	 * @param bool $useRelativePath User relative path
	 * @param int $maximumlength Maximum length
	 */
	public function __construct($applicationDir, $baseDir, $fileName, $prefix, $extension, $maximumlength, $useRelativePath)
	{
		$this->applicationDir = $applicationDir;
		$this->baseDir = $baseDir;
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
		$this->useRelativePath = $useRelativePath;
	}

	/**
	 * Set flag use relative path
	 * @param bool $useRelativePath use relative path
	 */
	public function setUseRelativePath($useRelativePath)
    {
        $this->useRelativePath = $useRelativePath;
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
     * Get relative from absolute path given
     * @param mixed $path Absolute path
     * @return mixed Relative path
     */
    public function getRelativePath($path)
    {
        $post = stripos($path, $this->applicationDir);
        if($post === 0)
        {
            return substr($path, strlen($this->applicationDir));
        } 
        else 
        {
            return $path;
        }
    }

	/**
	 * Get pooling path
	 * @return string Absolute pooling path
	 */
	public function getPoolPath()
	{
		if(!file_exists($this->baseDir))
		{
			$this->prepareDirectory($this->baseDir, $this->applicationDir, 0777, false);
		}
		$poolPath = $this->baseDir . "/" . $this->fileName . $this->extension;
		if(file_exists($poolPath) && filesize($poolPath) > $this->maximumlength)
		{
			$newPath = $this->baseDir . "/" . $this->prefix.date('Y-m-d-H-i-s') . "-" . $this->generateNewId() . $this->extension;
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
			if($this->useRelativePath)
			{
				$pathRel = $this->getRelativePath($path);
			}
			else
			{
				$pathRel = $path;
			}
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'CREATEFILE',
				'tm'=>$time,
				'path'=>$pathRel
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
			if($this->useRelativePath)
			{
				$pathRel = $this->getRelativePath($path);
			}
			else
			{
				$pathRel = $path;
			}
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'CREATEFILE',
				'tm'=>$time,
				'path'=>$pathRel
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
			if($this->useRelativePath)
			{
				$pathRel = $this->getRelativePath($path);
			}
			else
			{
				$pathRel = $path;
			}
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'DELETEFILE',
				'tm'=>$time,
				'path'=>$pathRel
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
			if($this->useRelativePath)
			{
				$oldPathRel = $this->getRelativePath($oldPath);
				$newPathRel = $this->getRelativePath($newPath);
			}
			else
			{
				$oldPathRel = $oldPath;
				$newPathRel = $newPath;
			}
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'RENAMEFILE',
				'tm'=>$time,
				'path'=>$oldPathRel,
				'to'=>$newPathRel
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
	public function prepareDirectory($dir2prepared, $dirBase, $permission, $sync = false)
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
			if($this->useRelativePath)
			{
				$pathRel = $this->getRelativePath($path);
			}
			else
			{
				$pathRel = $path;
			}
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'CREATEDIR',
				'tm'=>$time,
				'path'=>$pathRel
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
			if($this->useRelativePath)
			{
				$pathRel = $this->getRelativePath($path);
			}
			else
			{
				$pathRel = $path;
			}
			$time = time();
			$syncPath = $this->getPoolPath();
			$fp = fopen($syncPath, 'a');
			$syncContent = json_encode(array(
				'op'=>'DELETEDIR',
				'tm'=>$time,
				'path'=>$pathRel
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