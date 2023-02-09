<?php
class FileSynchronizer
{
	const NEW_LINE = "\r\n";
	public $basePath = '';
	public $fileName = 'pool.txt';
	public $prefix = 'pool_';
	public $extension = '.txt';
	/**
	 * Maximum file size
	 */
	private $maximumlength = 20000;
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
	public function getPoolPath()
	{
		$poolPath = $this->basePath . "/" . $this->fileName;
		if(file_exists($poolPath) && filesize($poolPath) > $this->maximumlength)
		{
			$newPath = $this->basePath . "/" . $this->prefix.date('Y-m-d-H-i-s')."-".$this->generateNewId().$this->extension;
			rename($poolPath, $newPath);
		}
		return $poolPath;
	}
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
			fwrite($fp, $syncContent.self::NEW_LINE);  
			fclose($fp);  
		}
	}
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