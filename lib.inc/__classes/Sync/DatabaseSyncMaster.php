<?php

namespace Sync;

class DatabaseSyncMaster extends \Sync\SyncMaster
{
    protected $database = null;
    protected $applicationRoot = '';
    protected $uploadBaseDir = '';
    protected $downloadBaseDir = '';
    protected $poolBaseDir = '';
    protected $poolFileName = '';
    protected $poolRollingPrefix = '';
    protected $poolFileExtension = '';
    protected $application = \Pico\PicoConst::PICO_EDU;

    /**
     * Constructor of DatabaseSyncMaster
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
        $this->database = $database;
        $this->applicationRoot = $applicationRoot;
        $this->uploadBaseDir = $uploadBaseDir;
        $this->downloadBaseDir = $downloadBaseDir;
        $this->poolBaseDir = $poolBaseDir;
        $this->poolFileName = $poolFileName;
        $this->poolRollingPrefix = $poolRollingPrefix;
        if ($poolFileExtension != null) {
            $this->poolFileExtension = $poolFileExtension;
        }
    }

    /**
     * List directory content
     * @param mixed $base Base directory
     * @return array|bool
     */
    protected function glob($base)
    {
        $basePath = $base . "/*.*";
        return glob($basePath);
    }

    /**
     * Rolling last polling file
     * @param array $fileList File list
     * @param string $poolBaseDir Pooling base directory
     * @param string $poolFileName Pooling file name
     * @param string $poolFileExtension Pooling file extension
     * @return array File list
     */
    protected function rollingLastPoolingFile($fileList, $poolBaseDir, $poolFileName, $poolFileExtension)
    {
        $pathToRemove = $poolBaseDir . "/" . $poolFileName . $poolFileExtension;
        foreach ($fileList as $key => $val) {
            if ($val == $pathToRemove) {
                $newPath = $this->poolBaseDir . "/" . $this->poolRollingPrefix . date('Y-m-d-H-i-s') . "-" . $this->generateNewId() . $this->poolFileExtension;
                rename($val, $newPath);
                $fileList[$key] = $newPath;
            }
        }
        return array_values($fileList);
    }

    /**
     * Sort file ascending. File name represent time create
     * @param array $fileList Array contain file list
     * @return array Array contain file list
     */
    protected function sort($fileList)
    {
        sort($fileList);
        return $fileList;
    }

    /**
     * Upload sync file to sync hub
     * @param mixed $path Sync file path
     * @param mixed $record Sync record
     * @param string $url Synch hub URL
     * @param string $username Sync username
     * @param string $password Sync password
     * @return mixed
     */
    protected function uploadSyncFile($path, $record, $fileSyncUrl, $username, $password)
    {
        $httpQuery = array(
            'application_id' => $this->application,
            'sync_type' => 'database',
            'action' => 'upload-sync-file'
        );
        $fileSyncUrl = $this->buildURL($fileSyncUrl, $httpQuery);
        ob_start();
        if (function_exists('curl_file_create')) {
            $cFile = curl_file_create($path);
        } else {
            $cFile = '@' . realpath($path);
        }

        $sync_database_id = $record['sync_database_id'];
        $file_path = $record['file_path'];
        $relative_path = $record['relative_path'];
        $file_name = $record['file_name'];
        $file_size = $record['file_size'];
        $time_create = $record['time_create'];
        $time_upload = $record['time_upload'];

        $post = array(
            'sync_database_id' => $sync_database_id,
            'file_path' => $file_path,
            'relative_path' => $relative_path,
            'file_name' => $file_name,
            'file_size' => $file_size,
            'time_create' => $time_create,
            'time_upload' => $time_upload,
            'file_contents' => $cFile
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_URL, $fileSyncUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        ob_end_clean();

        if ($httpcode) {
            return json_decode($server_output);
        } else {
            throw new \Sync\SyncException("Upload failed", $httpcode);
        }
    }

    /**
     * Get sync record list from database
     * @param string $direction Sync direction
     * @param array $status Sync record status
     * @return array
     */
    protected function getSyncRecordListFromDatabase($direction, $status)
    {
        $filter = "";
        if (is_array($status) && count($status) > 0) {
            $vals = array();
            foreach ($status as $val) {
                $val = addslashes($val);
                $vals[] = "`status` = '$val'";
            }
            $filter = " AND (" . implode(" OR ", $vals) . ") ";
        }
        $sql = "SELECT * FROM `edu_sync_database` WHERE `sync_direction` = '$direction' $filter ORDER BY  `edu_sync_database`.`time_create` ASC ";

        $stmt = $this->database->executeQuery($sql);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return array();
    }

    public function updateSyncRecord($sync_database_id, $status)
    {
        $sql = "UPDATE `edu_sync_database` SET `status` = '$status' WHERE `sync_database_id` = '$sync_database_id' ";
        return $this->database->executeUpdate($sql, false);
    }

    /**
     * Get relative from absolute path given
     * @param mixed $path Absolute path
     * @return mixed Relative path
     */
    public function getRelativePath($path)
    {
        $post = stripos($path, $this->applicationRoot);
        if ($post === 0) {
            return substr($path, strlen($this->applicationRoot));
        } else {
            return $path;
        }
    }

    /**
     * Get absolute from relative path given
     * @param mixed $path Relative path
     * @return mixed Absolute path
     */
    public function getAbsolutePath($path)
    {
        $post = stripos($path, $this->applicationRoot);
        if ($post === 0) {
            return $path;
        } else {
            return $this->applicationRoot . $path;
        }
    }

    protected function updatePathAndStatus($recordId, $absolutePath, $relativePath, $status)
    {
        $sql = "UPDATE `edu_sync_database` SET `file_path` = '$absolutePath', `relative_path` = '$relativePath', `status` = '$status' WHERE `sync_database_id` = '$recordId' ";
        return $this->database->executeQuery($sql);
    }

    /**
     * Get sync record
     * @param string $recordId Sync record ID
     * @return array|null Sync record if success and null if failed
     */
    public function getSyncRecord($recordId)
    {
        $recordId = addslashes($recordId);
        $sql = "SELECT * FROM `edu_sync_database` WHERE `sync_database_id` = '$recordId' ";
        $stmt = $this->database->executeQuery($sql);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return null;
    }

    protected function prepareDirectory($dir)
    {
        if (!file_exists($dir)) {
            $this->database->getDatabaseSyncConfig()->prepareDirectory($dir, $this->applicationRoot, 0755);
        }
    }

    /**
     * Generate 20 bytes unique ID
     * @return string 20 bytes
     */
    public function generateNewId()
    {
        $uuid = uniqid();
        if ((strlen($uuid) % 2) == 1) {
            $uuid = '0' . $uuid;
        }
        $random = sprintf('%06x', mt_rand(0, 16777215));
        return sprintf('%s%s', $uuid, $random);
    }



    /**
     * Get the value of application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the value of application
     *
     * @return self
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }
}
