<?php

namespace Sync;

class FileSyncUpload extends \Sync\FileSyncMaster
{

    /**
     * Move pooling file to new path and return the new file list
     */
    private function getPoolingFiles()
    {
        $fileList = $this->glob($this->poolBaseDir);
        $fileList = $this->rollingLastPoolingFile($fileList, $this->poolBaseDir, $this->poolFileName, $this->poolFileExtension);
        $fileList = $this->sort($fileList);
        return $fileList;
    }

    /**
     * Create sync record to database
     * @param string $localPath Sync file path
     * @return void
     */
    private function createUploadSyncRecord($localPath)
    {
        $fileSize = filesize($localPath) * 1;
        $baseName = addslashes(basename($localPath));
        $path = addslashes($localPath);
        $relativePath = $this->getRelativePath($localPath);
        $sync_file_id = $this->database->generateNewId();
        $timeUpload = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `edu_sync_file`
        (`sync_file_id`, `file_path`, `relative_path`, `file_name`, `file_size`, `sync_direction`, `time_create`, `time_upload`, `status`) VALUES
        ('$sync_file_id', '$path', '$relativePath', '$baseName', '$fileSize', 'up', '$timeUpload', '$timeUpload', 0)";
        $this->database->execute($sql);
    }

    /**
     * Move pooling sync file and record to database before send the sync file (step 1 and 2)
     * @return bool
     */
    public function syncLocalUserFileToDatabase()
    {
        $fileList = $this->getPoolingFiles();
        foreach ($fileList as $localPath) {
            $baseName = basename($localPath);
            $this->prepareDirectory($this->uploadBaseDir);
            $newPath = $this->uploadBaseDir . "/" . $baseName;
            copy($localPath, $newPath);
            unlink($localPath);
            $this->createUploadSyncRecord($newPath);
        }

        return true;
    }

    /**
     * Move pooling sync file and record to database before send the sync file (step 1 and 2)
     * @return bool
     */
    public function fileUploadPreparation()
    {
        return $this->syncLocalUserFileToDatabase();
    }

    /**
     * Sync all local user file to sync hub and upload file (step 3, 4 and 5)
     * @param string $recordId Sync record ID
     * @param string $fileSyncUrl Sync hub URL
     * @param string $username Sync hub username
     * @param string $password Sync hub password
     */
    public function fileUploadSyncFiles($recordId, $fileSyncUrl, $username, $password)
    {
        $record = $this->getSyncRecord($recordId);
        $path = $record['file_path'];
        $sync_file_id = $record['sync_file_id'];

        if (file_exists($path)) {
            $response = $this->uploadSyncFile($path, $record, $fileSyncUrl, $username, $password);
            if (!empty($response)) {
                $this->updateSyncRecord($sync_file_id, 2);
            } else {
                $this->updateSyncRecord($sync_file_id, 1);
            }
        }
    }

    /**
     * Upload user files
     *
     * @param string $recordId
     * @param string $fileSyncUrl
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function fileUploadUserFiles($recordId, $fileSyncUrl, $username, $password)
    {
        try {
            $record = $this->getSyncRecord($recordId);
            if ($record != null) {
                $syncFilePath = $record['file_path'];
                if (file_exists($syncFilePath)) {
                    $handle = fopen($syncFilePath, "r");
                    if ($handle) {
                        while (($line = fgets($handle)) !== false) {
                            $this->processLine($line, $fileSyncUrl, $username, $password);
                        }
                    }
                    $this->updateSyncRecord($recordId, 1);
                    return true;
                }
            }
        } catch (\Sync\SyncException $e) {
            // Do nothing
        }
        return true;
    }

    /**
     * Process line
     *
     * @param string $line
     * @param string $fileSyncUrl
     * @param string $username
     * @param string $password
     * @return void
     */
    public function processLine($line, $fileSyncUrl, $username, $password)
    {
        $info = json_decode($line, true);
        if ($info['op'] == 'CREATEFILE') {
            $path = $this->getPath($info);
            $this->uploadUserFile($path, $fileSyncUrl, $username, $password);
        }
    }

    /**
     * Get apth
     *
     * @param array $info
     * @return string
     */
    public function getPath($info)
    {
        $path = $info['path'];
        if ($this->useRelativePath) {
            $path = $this->getAbsolutePath($path);
        }
        return $path;
    }
}
