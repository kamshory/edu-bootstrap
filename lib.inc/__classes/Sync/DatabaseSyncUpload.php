<?php

namespace Sync;

class DatabaseSyncUpload extends \Sync\DatabaseSyncMaster
{
    /**
     * Constructor of DatabaseSyncUpload
     * @param \Pico\PicoDatabase $database Database
     * @param string $applicationRoot Application root
     * @param string $uploadBaseDir Upload base direcory
     * @param string $downloadBaseDir Download base directory
     * @param string $poolBaseDir Pooling file base directory
     * @param string $poolFileName Pooling file name
     * @param string $poolRollingPrefix Pooling file prefix
     * @param string $poolFileExtension Pooling file extension
     */
    public function __construct($database, $applicationRoot, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension = null) //NOSONAR
    {
        parent::__construct($database, $applicationRoot, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension);
    }

    /**
     * Move pooling file to new path and return the new file list
     */
    private function movePoolingFileToUpload()
    {
        $this->prepareDirectory($this->uploadBaseDir);
        $fileList = $this->glob($this->poolBaseDir);
        $fileList = $this->rollingLastPoolingFile($fileList, $this->poolBaseDir, $this->poolFileName, $this->poolFileExtension);
        $fileList = $this->sort($fileList);
        $fileToUpload = array();
        foreach ($fileList as $val) {
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
    private function createUploadSyncRecord($localPath)
    {
        $fileSize = filesize($localPath) * 1;
        $baseName = addslashes(basename($localPath));
        $path = addslashes($localPath);
        $relativePath = $this->getRelativePath($localPath);
        $sync_database_id = $this->database->generateNewId();
        $timeUpload = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `edu_sync_database`
        (`sync_database_id`, `file_path`, `relative_path`, `file_name`, `file_size`, `sync_direction`, `time_create`, `time_upload`, `status`) VALUES
        ('$sync_database_id', '$path', '$relativePath', '$baseName', '$fileSize', 'up', '$timeUpload', '$timeUpload', 0)
        ";
        $this->database->execute($sql);
    }

    /**
     * Move pooling sync file and record to database before send the sync file (step 1 and 2)
     */
    public function syncLocalQueryToDatabase()
    {
        $fileList = $this->movePoolingFileToUpload();
        foreach ($fileList as $val) {
            $this->createUploadSyncRecord($val);
        }
        return true;
    }

    /**
     * Sync all local user file to remote host and upload file (step 3, 4 and 5)
     */
    public function syncLocalQueryToRemoteHost($url, $username, $password)
    {
        $records = $this->getSyncRecordListFromDatabase('up', array(0,));
        foreach ($records as $record) {
            $path = $record['file_path'];
            $sync_database_id = $record['sync_database_id'];
            if ($this->uploadSyncFile($path, $record, $url, $username, $password)) {
                $this->updateSyncRecord($sync_database_id, 1);
            }
        }
    }
    public function databasePrepareUploadSyncFiles()
    {
        return $this->getSyncRecordListFromDatabase('up', array(0));
    }

    /**
     * Sync all local user file to sync hub and upload file (step 3, 4 and 5)
     * @param string $recordId Sync record ID
     * @param string $fileSyncUrl Sync hub URL
     * @param string $username Sync hub username
     * @param string $password Sync hub password
     */
    public function databaseUploadSyncFiles($recordId, $fileSyncUrl, $username, $password)
    {
        $record = $this->getSyncRecord($recordId);
        $path = $record['file_path'];
        if (file_exists($path)) {
            try {
                $result = $this->uploadSyncFile($path, $record, $fileSyncUrl, $username, $password); // NOSONAR            
                $this->updateSyncRecord($recordId, 1);
            } catch (\Exception $e) {
                $this->updateSyncRecord($recordId, 1);
            }
        }
        return true;
    }
}
