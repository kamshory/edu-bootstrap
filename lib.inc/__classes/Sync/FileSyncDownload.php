<?php

namespace Sync;

class FileSyncDownload extends \Sync\FileSyncMaster
{
    /**
     * (step 1, 2 and 3)
     * @param string $fileSyncUrl Synch hub URL
     * @param string $username Sync username
     * @param string $password Sync password
     * @return bool
     */
    public function fileDownloadInformation($fileSyncUrl, $username, $password)
    {
        $lastSync = $this->getLastSyncTime();
        if ($lastSync === null) {
            $lastSync = '0000-00-00 00:00:00';
        }
        try {
            $response = $this->getSyncRecordListFromRemote($lastSync, $fileSyncUrl, $username, $password);
            if ($response['response_code'] == \Sync\SyncResponseCode::SUCCESS) {
                $recordList = $response['data'];
                return $this->createDownloadSyncRecord($recordList);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get last sync time
     * @return mixed Last sync time if exists or null not exists
     */
    private function getLastSyncTime()
    {
        $sql = "SELECT * FROM `edu_sync_file` WHERE `sync_direction` = 'down' AND `status` > 0 ORDER BY `time_create` DESC LIMIT 0,1 ";
        $stmt = $this->database->executeQuery($sql);
        if($stmt->rowCount() > 0) {
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $data['time_create'];
        }
        return null;
    }

    /**
     * Get record list from remote host
     * @param string $lastSync Last sync time
     * @param string $fileSyncUrl Synch hub URL
     * @param string $username Sync username
     * @param string $password Sync password
     * @return array List of sync file from last sync
     */
    private function getSyncRecordListFromRemote($lastSync, $fileSyncUrl, $username, $password) //NOSONAR
    {
        $httpQuery = array(
            'application_id' => $this->application,
            'sync_type' => 'file',
            'action' => 'list-record',
            'last_sync' => $lastSync
        );
        $fileSyncUrl = $this->buildURL($fileSyncUrl, $httpQuery);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_URL, $fileSyncUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpcode == 200) {
            return json_decode($server_output, true);
        } else {
            throw new \Sync\SyncException("File not found");
        }
    }

    /**
     * Download file from remote host and copy it into local path
     * @param string $remotePath Remote path
     * @param string $localPath Local path
     * @param string $fileSyncUrl Synch hub URL
     * @param string $username Sync username
     * @param string $password Sync password
     * @return string Data from file downloaded
     * @throws \Sync\SyncException
     */
    public function downloadFileFromRemote($relativePath, $fileSyncUrl, $username, $password) //NOSONAR
    {
        $url = rtrim($fileSyncUrl, "/") . "/" . ltrim($relativePath, "/");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            return $server_output;
        } else {
            throw new \Sync\SyncException("File not found", $httpcode);
        }
    }

    /**
     * Create download sync record
     * @param array $recordList Record list
     */
    private function createDownloadSyncRecord($recordList)
    {
        foreach ($recordList as $record) {
            $fileSize = ((int) $record['file_size']);
            $sync_file_id = addslashes($record['sync_file_id']);
            $time_create = addslashes($record['time_create']);
            $baseName = addslashes($record['file_name']);
            $relative_path = addslashes($record['relative_path']);
            $time_upload = addslashes($record['time_upload']);
            $time_download = date('Y-m-d H:i:s');


            $localPath = $this->downloadBaseDir . "/" . $baseName;

            $localPath = addslashes($localPath);
            $sql = "INSERT INTO `edu_sync_file`
            (`sync_file_id`, `file_path`, `relative_path`, `file_name`, `file_size`, `sync_direction`, `time_create`, `time_upload`, `time_download`, `status`) VALUES
            ('$sync_file_id', '$localPath', '$relative_path', '$baseName', '$fileSize', 'down', '$time_create', '$time_upload', '$time_download', 0)";
            $this->database->execute($sql);
        }
        return true;
    }

    /**
     * Synchronize user files 
     * @param mixed $permission File permission
     * @param mixed $fileSyncUrl Sync hub URL
     * @param mixed $username Sync hub username
     * @param mixed $password Sync hub password
     * @return void
     */
    public function syncRemoteUserFileLocalHost($record, $permission, $fileSyncUrl, $username, $password)
    {
        $this->syncUserFilesFromSyncRecord($record, $permission, $fileSyncUrl, $username, $password);
    }

    public function fileDownloadSyncFiles($recordId, $permission, $fileSyncUrl, $username, $password)
    {
        try {
            $record = $this->getSyncRecord($recordId);
            if ($record != null) {
                $relativePath = $record['relative_path'];
                $absolutePath = $this->downloadBaseDir . "/" . basename($relativePath);

                $content = $this->downloadFileFromRemote($relativePath, $fileSyncUrl, $username, $password);

                $dir = dirname($absolutePath);
                $this->prepareDirectory($dir);

                file_put_contents($absolutePath, $content);
                chmod($absolutePath, $permission);
                $this->updatePathAndStatus($recordId, $absolutePath, $relativePath, 1);
                return true;
            }
        } catch (\Sync\SyncException $e) {
            $this->updateSyncRecord($recordId, 1);
        }
        return true;
    }

    public function fileDownloadUserFiles($recordId, $permission, $fileSyncUrl, $username, $password)
    {
        try {
            $record = $this->getSyncRecord($recordId);
            if ($record != null) {
                $this->syncUserFilesFromSyncRecord($record, $permission, $fileSyncUrl, $username, $password);
                $this->updateSyncRecord($recordId, 2);
                return true;
            }
        } catch (\Sync\SyncException $e) {
            // DO nothing
        }
        return true;
    }

    /**
     * Synchronize user file from sync record
     * @param mixed $record Sync record
     * @param mixed $permission File permission
     * @param mixed $fileSyncUrl Sync hub URL
     * @param mixed $username Sync hub username
     * @param mixed $password Sync hub password
     * @return bool
     */
    private function syncUserFilesFromSyncRecord($record, $permission, $fileSyncUrl, $username, $password)
    {
        $syncFilePath = rtrim($this->downloadBaseDir, "/") . "/" . basename($record['relative_path']);
        if (file_exists($syncFilePath)) {
            $handle = fopen($syncFilePath, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $info = json_decode($line, true);

                    if ($info['op'] == 'CREATEFILE') {
                        $this->procCreateFile($info, $permission, $fileSyncUrl, $username, $password);
                    } else if ($info['op'] == 'RENAMEFILE') {
                        $this->procRenameFile($info, $permission, $fileSyncUrl, $username, $password);
                    } else if ($info['op'] == 'DELETEFILE') {
                        $this->procDeleteFile($info);
                    } else if ($info['op'] == 'CREATEDIR') {
                        $this->procCreateDir($info, $permission);
                    } else if ($info['op'] == 'RENAMEDIR') {
                        $this->procRenameDir($info, $permission);
                    }
                }
                fclose($handle);
            }
        }
        return true;
    }

    /**
     * Download user file from sync hub and create on local host and set its permission
     * @param mixed $info File info from sync record
     * @param mixed $permission File permission
     * @param mixed $fileSyncUrl Sync hub URL
     * @param mixed $username Sync hub username
     * @param mixed $password Sync hub password
     * @return void
     */
    public function procCreateFile($info, $permission, $fileSyncUrl, $username, $password)
    {
        try {
            $tm = $info['tm'];

            if ($this->useRelativePath) {
                $relativePath = $info['path'];
                $localPath = rtrim($this->applicationRoot, "/") . "/" . ltrim($relativePath);
            } else {
                $localPath = $info['path'];
                $relativePath = $this->getRelativePath($localPath);
            }
            if($this->isAllowedFileExtension($relativePath))
            {
                $response = $this->downloadFileFromRemote($relativePath, $fileSyncUrl, $username, $password);
                $dir = dirname($localPath);
                $this->prepareDirectory($dir);
                file_put_contents($localPath, $response);
                touch($localPath, $tm);
                chmod($localPath, $permission);
            }
        } catch (\Sync\SyncException $e) {
            //NOSONAR
        } catch (\Exception $e) {
            //NOSONAR
        }
    }

    public function isAllowedFileExtension($path)
    {
        $ext = $this->getFileExtension($path);
        return !in_array($ext, $this->forbiddenExtension);
    }

    /**
     * Rename user file on local host if exists or download it if not exists and set its permission
     * @param mixed $info File info from sync record
     * @param mixed $permission File permission
     * @param mixed $fileSyncUrl Sync hub URL
     * @param mixed $username Sync hub username
     * @param mixed $password Sync hub password
     * @return void
     */
    public function procRenameFile($info, $permission, $fileSyncUrl, $username, $password)
    {
        $tm = $info['tm'];
        $oldName = $info['path']; // Assumed path is absolute
        $newName = $info['to']; // Assumed path is absolute

        if ($this->useRelativePath) {
            $relativePath = $info['to']; // Relative
            $oldName = rtrim($this->applicationRoot, "/") . "/" . ltrim($oldName); // Absolute
            $newName = rtrim($this->applicationRoot, "/") . "/" . ltrim($newName); // Absolute
        } else {
            $relativePath = $this->getRelativePath($newName); //Relative
        }

        if (file_exists($oldName) && !file_exists($newName)) {
            chmod($oldName, 0777);
            $dir = dirname($newName);
            if (!file_exists($dir)) {
                $this->prepareDirectory($dir);
            }
            rename($oldName, $newName);
            chmod($newName, $permission);
        } else {
            // force download
            try {
                $response = $this->downloadFileFromRemote($relativePath, $fileSyncUrl, $username, $password);
                $dir = dirname($newName);
                $this->prepareDirectory($dir);
                file_put_contents($newName, $response);
                touch($newName, $tm);
            } catch (\Sync\SyncException $e) {
                //NOSONAR
            } catch (\Exception $e) {
                //NOSONAR
            }
        }
    }

    /**
     * Delete user file on local host
     * @param mixed $info File info from sync record
     * @return void
     */
    public function procDeleteFile($info)
    {
        $localPath = $info['path'];
        if ($this->useRelativePath) {
            $localPath = rtrim($this->applicationRoot, "/") . "/" . ltrim($localPath);
        }
        if (file_exists($localPath)) {
            chmod($localPath, 0777);
            unlink($localPath);
        }
    }

    /**
     * Create direcory on local host and set its permission
     * @param mixed $info File info from sync record
     * @param mixed $permission File permission
     * @return void
     */
    public function procCreateDir($info, $permission)
    {
        $localPath = $info['path'];
        if ($this->useRelativePath) {
            $localPath = rtrim($this->applicationRoot, "/") . "/" . ltrim($localPath);
        }
        if (!file_exists($localPath)) {
            $this->prepareDirectory($localPath, $permission);
        }
    }

    /**
     * Rename directory and set its permission
     * @param mixed $info File info from sync record
     * @param mixed $permission File permission
     * @return void
     */
    public function procRenameDir($info, $permission)
    {
        $oldName = $info['path']; // Assumed path is absolute
        $newName = $info['to']; // Assumed path is absolute

        if ($this->useRelativePath) {
            $oldName = rtrim($this->applicationRoot, "/") . "/" . ltrim($oldName); // Absolute
            $newName = rtrim($this->applicationRoot, "/") . "/" . ltrim($newName); // Absolute
        }

        if (!file_exists($newName)) {
            if (file_exists($oldName)) {
                chmod($oldName, 0777);
                rename($oldName, $newName);
                chmod($newName, $permission);
            } else {
                // force create new direcory
                mkdir($newName, $permission);
            }
        }
    }
}




/**
 * File Sync Upload
 * 1. Move pooling file to upload directory
 * 2. Create sync record to database
 * 3. Upload sync file to remote host
 * 4. Upload user file to remote host
 * 5. Update sync record status
 * 
 * File Sync Download
 * 1. Get sync record from remote host
 * 2. Download sync file from remote host
 * 3. Create sync record to database
 * 4. Sync user file : CREATEFILE=>DOWNLOAD, RENAMEFILE=>RENAME if exists or DOWNLOAD if not exists, CREATEDIR, RENAMEDIR, DELETEDIR
 * 5. Update sync record status
 */
