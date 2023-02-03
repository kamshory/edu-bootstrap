<?php

class DatabaseSyncMaster
{
    public $database = null;
    public $uploadBaseDir = '';
    public $downloadBaseDir = '';
    public $poolBaseDir = '';
    public $poolFileName = '';
    public $poolRollingPrefix = '';
    public $poolFileExtension = '.txt';
    public function __construct($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension = null)
    {
        $this->database = $database;
        $this->uploadBaseDir = $uploadBaseDir;
        $this->downloadBaseDir = $downloadBaseDir;
        $this->poolBaseDir = $poolBaseDir;
        $this->poolFileName = $poolFileName;
        $this->poolRollingPrefix = $poolRollingPrefix;
        if($poolFileExtension != null)
        {
            $this->poolFileExtension = $poolFileExtension;
        }
    }
    protected function glob($base)
    {
        $basePath = $base.".*";
        $list = array();
        foreach(glob($basePath) as $filename) 
        {
            $list[] = $base."/".$filename;
        }
        return $list;
    }
    protected function filterPoolingFileList($fileList, $poolBaseDir, $poolFileName, $poolFileExtension)
    {
        $pathToRemove = $poolBaseDir ."/". $poolFileName . $poolFileExtension;
        foreach($fileList as $key=>$val)
        {
            if($val == $pathToRemove)
            {
                unset($fileList[$key]);
                return array_values($fileList);
            }
        }
        return array_values($fileList);
    }
    protected function sort($fileList)
    {
        sort($fileList);
        return $fileList;
    }

    protected function uploadSyncFile($path, $record, $url, $username, $password)
    {
        if(function_exists('curl_file_create')) 
        { 
            $cFile = curl_file_create($path);
        } 
        else 
        {  
            $cFile = '@' . realpath($path);
        }

        $sync_file_id = $record['sync_file_id'];
        $file_path = $record['file_path'];
        $file_name = $record['file_name'];
        $file_size = $record['file_size'];
        $time_create = $record['time_create'];
        $time_upload = $record['time_upload'];
        
        $post = array(
            'sync_file_id' => $sync_file_id,
            'file_path' => $file_path,
            'file_name' => $file_name,
            'file_size' => $file_size,
            'time_create' => $time_create,
            'time_upload' => $time_upload,
            'file_contents' => $cFile
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec ($ch);
        curl_close ($ch);
        return true;
    }

    protected function getSyncRecordListFromDatabase($direction, $status)
    {
        $sql = "SELECT * FROM `edu_sync_database` WHERE `sync_direction` = '$direction' AND `status` = '$status' ";
        $stmt = $this->database->executeQuery($sql);
        if($stmt->rowCount() > 0)
        {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return array();
    }
    public function updateSyncRecord($sync_file_id, $status)
    {
        $sql = "UPDATE `edu_sync_database` SET `status` = '$status' WHERE `sync_file_id` = '$sync_file_id' ";
        return $this->database->executeUpdate($sql, false);
    }
}

class DatabaseSyncUpload extends DatabaseSyncMaster
{
    public function __construct($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension = null)
    {
        parent::__construct($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension);      
    }
    

    /**
     * Move pooling file to new path and return the new file list
     */
    private function movePoolingFileToUpload()
    {
        $fileList = $this->glob($this->poolBaseDir);
        $fileList = $this->filterPoolingFileList($fileList, $this->poolBaseDir, $this->poolFileName, $this->poolFileExtension);
        $fileList = $this->sort($fileList);
        $fileToUpload = array();
        foreach($fileList as $val)
        {
            $baseName = basename($val);
            $newPath = $this->uploadBaseDir . "/" . $baseName;
            copy($val, $newPath);
            unlink($val);
            $fileToUpload[] = $newPath;
        }
        return $fileToUpload;
    }

    /**
     * Create sync record to database
     */
    private function createUploadSyncRecord($val)
    {
        $fileSize = filesize($val) * 1;
        $baseName = addslashes(basename($val));
        $path = addslashes($val);
        $sync_file_id = $this->database->generateNewId();
        $timeUpload = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `edu_sync_database`
        (`sync_file_id`, `file_path`, `file_name`, `file_size`, `sync_direction`, `time_create`, `time_upload`, `status`) VALUES
        ('$sync_file_id', '$path', '$baseName', '$fileSize', 'up', '$timeUpload', '$timeUpload', 0)";
        $this->database->execute($sql);
        $this->database->getDatabaseConnection()->getLastId();
    }

    /**
     * Move pooling sync file and record to database before send the sync file (step 1 and 2)
     */
    public function syncLocalQueryToDatabase()
    {
        $fileList = $this->movePoolingFileToUpload();
        foreach($fileList as $val)
        {
            $this->createUploadSyncRecord($val);
        }
    }
    
    /**
     * Sync all local user file to remote host and upload file (step 3, 4 and 5)
     */
    public function syncLocalQueryToRemoteHost($url, $username, $password)
    {
        $records = $this->getSyncRecordListFromDatabase('up', 0);
        foreach($records as $record)
        {
            $path = $record['file_path'];
            $sync_file_id = $record['sync_file_id'];
            if($this->uploadSyncFile($path, $record, $url, $username, $password))
            {
                $this->updateSyncRecord($sync_file_id, 1);
            }
        }
    }
}

class DatabaseSyncDownload extends DatabaseSyncMaster
{
    public function __construct($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension = null)
    {
        parent::__construct($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension);      
    }
    
    /**
     * (step 1, 2 and 3)
     */
    public function syncRemoteHostRecordToDatabase($url, $username, $password)
    {
        $lastSync = $this->getLastSyncTime();
        if($lastSync === null)
        {
            $lastSync = '0000-00-00 00:00:00';
        }
        $recordList = $this->getSyncRecordListFromRemote($lastSync, $url, $username, $password);
        $this->createDownloadSyncRecord($recordList, $url, $username, $password);
    }

    private function getLastSyncTime()
    {
        $sql = "SELECT * FROM `edu_sync_database` WHERE `sync_direction` = 'down' AND `status` > 0 ORDER BY `time_create` DESC LIMIT 0,1 ";
        $stmt = $this->database->executeQuery($sql);
        if($stmt->rowCount() > 0)
        {
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data['time_create'];
        }
        return null;
    }

    /**
     * Get record list from remote host
     * @param string $lastSync Last sync time
     * @param string $url Remote host URL
     * @param string $username Sync username
     * @param string $password Sync password
     * @return array List of sync file from last sync
     */
    private function getSyncRecordListFromRemote($lastSync, $url, $username, $password) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'last_sync'=>$lastSync
                    )
            )
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        
        if($httpcode)
        {
            return json_decode($server_output);
        }
        else
        {
            throw new Exception("File not found");
        }
    }

     /**
     * Download file from remote host and copy it into local path
     * @param string $remotePath Remote path
     * @param string $localPath Local path
     * @param string $url Remote host URL
     * @param string $username Sync username
     * @param string $password Sync password
     * @return string Data from file downloaded
     */
    public function downloadFileFromRemote($remotePath, $url, $username, $password)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(
                array('file_path'=>$remotePath)
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpcode == 200)
        {
            return $server_output;
        }
        else
        {
            throw new Exception("File not found");
        }
    }
    

    private function createDownloadSyncRecord($recordList, $url, $username, $password)
    {
        foreach($recordList as $record)
        {
            $fileSize = ((int) $record['file_size']);
            $baseName = addslashes($record['file_name']);
            $sync_file_id = addslashes($record['sync_file_id']);
            $time_create = addslashes($record['time_create']);
            $baseName = addslashes($record['file_name']);
            $remote_path = addslashes($record['file_path']);
            $time_upload = addslashes($record['time_upload']);
            $time_download = date('Y-m-d H:i:s');

            $localPath = $this->downloadBaseDir . "/" . $baseName;

            try
            {
                $response = $this->downloadFileFromRemote($remote_path, $url, $username, $password);
                if(file_put_contents($localPath, $response))
                {   
                    $localPath = addslashes($localPath);
                    $sync_file_id = $this->database->generateNewId();
                    $sql = "INSERT INTO `edu_sync_database`
                    (`sync_file_id`, `file_path`, `file_name`, `file_size`, `sync_direction`, `time_create`, `time_upload`, `time_download`, `status`) VALUES
                    ('$sync_file_id', '$localPath', '$baseName', '$fileSize', 'down', '$time_create', '$time_upload', '$time_download', 0)";
                    $this->database->execute($sql);
                }
            }
            catch(Exception $e)
            {
                // Do nothing
            }
        }
    }

    public function syncRemoteQueryToDatabase()
    {
        $recordList = $this->getSyncRecordListFromDatabase('down', 0);
        foreach($recordList as $record)
        {
            $this->syncQuerysFromSyncRecord($record);
        }
    }
    
    private function syncQuerysFromSyncRecord($record)
    {
        $syncFilePath = addslashes($record['file_path']);
        $delimiter = trim($this->database->syncDatabaseDelimiter);

        $handle = fopen($syncFilePath, "r");
        if ($handle) {
            $buff = "";
            while (($line = fgets($handle)) !== false) {
                $chk = trim($line);
                if($chk == $delimiter)
                {
                    $this->execute($buff);
                    $buff = "";
                }
                else
                {
                    $buff .= $line."\r\n";
                }
            }
            fclose($handle);
        }

    }
    private function execute($sql)
    {
        $sql = trim($sql);
        if(!empty($sql))
        {
            $this->database->execute($sql);
        }
    }
}




/**
 * Database Sync Upload
 * 1. Move pooling file to upload directory
 * 2. Create sync record to database
 * 3. Upload sync file to remote host
 * 5. Update sync record status
 * 
 * Database Sync Download
 * 1. Get sync record from remote host
 * 2. Download sync file from remote host
 * 3. Create sync record to database
 * 4. Execute every query file sync file
 * 5. Update sync record status
 */