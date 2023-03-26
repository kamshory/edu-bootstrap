<?php

namespace Sync;

class DatabaseSyncDownload extends \Sync\DatabaseSyncMaster
{
    /**
     * Download database information
     *
     * @param string $url
     * @param string $username
     * @param string $password
     * @return mixed
     */
    public function databaseDownloadInformation($url, $username, $password)
    {
        $lastSync = $this->getLastSyncTime();
        if ($lastSync === null) {
            $lastSync = '0000-00-00 00:00:00';
        }
        try {
            $response = $this->getSyncRecordListFromRemote($lastSync, $url, $username, $password);
            if ($response['response_code'] == \Sync\SyncResponseCode::SUCCESS) {
                $recordList = $response['data'];
                return $this->createDownloadSyncRecord($recordList);
            }
        } catch (\Exception $e) {
            // Do nothing
        }
        return true;
    }

    /**
     * Prepare download sync file
     *
     * @return mixed
     */
    public function databasePrepareDownloadSyncFiles()
    {
        return $this->getSyncRecordListFromDatabase('down', array(0));
    }

    /**
     * Prepare execute query
     *
     * @return mixed
     */
    public function databasePrepareExecuteQuery()
    {
        return $this->getSyncRecordListFromDatabase('down', array(0, 1));
    }

    /**
     * Get last sync time
     *
     * @return string|null
     */
    private function getLastSyncTime()
    {
        $sql = "SELECT * FROM `edu_sync_database` 
        WHERE `sync_direction` = 'down' 
        AND `status` > 0 
        ORDER BY `time_create` DESC 
        LIMIT 0,1 ";
        $stmt = $this->database->executeQuery($sql);
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
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
     * @throws \Sync\SyncException
     */
    private function getSyncRecordListFromRemote($lastSync, $fileSyncUrl, $username, $password)
    {
        $httpQuery = array(
            'application_id' => $this->application,
            'sync_type' => 'database',
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

        if ($httpcode) {
            return json_decode($server_output, true);
        } else {
            throw new \Sync\SyncException("File not found");
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
     * @throws \Sync\SyncException
     */
    public function downloadFileFromRemote($relativePath, $fileSyncUrl, $username, $password)
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
            throw new SyncException("File not found", $httpcode);
        }
    }

    /**
     * Download database sync file
     *
     * @param string $recordId
     * @param string $permission
     * @param string $fileSyncUrl
     * @param string $username
     * @param string $password
     * @return \PDOStatement|bool
     */
    public function databaseDownloadSyncFiles($recordId, $permission, $fileSyncUrl, $username, $password)
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
                $absolutePath = addslashes($absolutePath);
                $relativePath = addslashes($relativePath);
                $this->updatePathAndStatus($recordId, $absolutePath, $relativePath, 1);
            }
        } catch (\Sync\SyncException $e) {
            $this->updateSyncRecord($recordId, 1);
        }
        return true;
    }

    /**
     * Create download sync record
     *
     * @param array $recordList
     * @return bool
     */
    private function createDownloadSyncRecord($recordList)
    {
        foreach ($recordList as $record) {
            $fileSize = ((int) $record['file_size']);
            $sync_database_id = addslashes($record['sync_database_id']);
            $time_create = addslashes($record['time_create']);
            $baseName = addslashes($record['file_name']);
            $relative_path = addslashes($record['relative_path']);
            $time_upload = addslashes($record['time_upload']);
            $time_download = date('Y-m-d H:i:s');
            $localPath = $this->downloadBaseDir . "/" . $baseName;
            $localPath = addslashes($localPath);

            $sql = "INSERT INTO `edu_sync_database`
            (`sync_database_id`, `file_path`, `relative_path`, `file_name`, `file_size`, `sync_direction`, `time_create`, `time_upload`, `time_download`, `status`) VALUES
            ('$sync_database_id', '$localPath', '$relative_path', '$baseName', '$fileSize', 'down', '$time_create', '$time_upload', '$time_download', 0)";
            $this->database->execute($sql);
        }
        return true;
    }

    /**
     * Execute all queries from selected sync record
     * @param array $record Sync record
     */
    private function syncQuerysFromSyncRecord($record)
    {
        $syncFilePath = $record['file_path'];
        $delimiter = trim($this->database->getDatabaseSyncConfig()->getDelimiter());
        if (file_exists($syncFilePath)) {
            $handle = fopen($syncFilePath, "r");
            if ($handle) {
                $buff = "";
                while (($line = fgets($handle)) !== false) {
                    $chk = trim($line);
                    if ($chk == $delimiter) {
                        $this->executeQuery($buff);
                        $buff = "";
                    } else {
                        $buff .= $line . "\r\n";
                    }
                }
                fclose($handle);
            }
        }
    }

    /**
     * Execute all queries from selected sync record and update record status
     * @param string $recordId Sync record ID
     * @return bool true if success and false if failed
     */
    public function databaseExecuteQuery($recordId)
    {
        $record = $this->getSyncRecord($recordId);
        $this->syncQuerysFromSyncRecord($record);
        $this->updateSyncRecord($recordId, 2);
        return true;
    }

    /**
     * Execute database query
     * @param string $sql Database query to be executed
     * @return bool true if success and false if failed
     */
    private function executeQuery($sql)
    {
        $sql = trim($sql);
        if (!empty($sql)) {
            /**
             * Old code
             * $this->database->execute($sql);
             */
            $stmt = $this->database->getDatabaseConnection()->prepare($sql);
            try {
                $stmt->execute();
            } catch (\PDOException $e) {
                // Do nothing
            }
        }
        return true;
    }
}
