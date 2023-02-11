<?php
include_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-sync.php";

if(@$_GET['type'] == 'file' || @$_GET['type'] == 'database' || @$_GET['action'] == 'ping')
{
    $syncHubURL = $database->getSystemVariable("sync_hub_url");
    $fileSyncUrl = rtrim($syncHubURL, "/")."/";
    $databaseSyncUrl = rtrim($syncHubURL, "/")."/";

    $username = $database->getSystemVariable("sync_hub_username");

    $password = $database->getSystemVariable("sync_hub_password");   
}

class SyncPing
{
    protected function buildURL($url, $httpQuery, $keepOriginal = true)
    {
        $original = array();
        if($keepOriginal)
        {
            $parsed = parse_url($url);
            if(isset($parsed['query']))
            {
                parse_str($parsed['query'], $original);
            }
        }
        $combined = array_merge($original, $httpQuery);
        
        if(stripos($url, "?") !== false)
        {
            $arr = explode("?", $url);
            $url = $arr[0];
        }        
        $url = $url."?".http_build_query($combined);
        return $url;
    }

    /**
     * Upload sync file to sync hub
     * @param string $fileSyncUrl Synch hub URL
     * @param string $username Sync username
     * @param string $password Sync password
     * @return array
     */
    public function ping($fileSyncUrl, $username, $password) //NOSONAR
    {
        $httpQuery = array(
            'action'=>'ping'
        );
        $fileSyncUrl = $this->buildURL($fileSyncUrl, $httpQuery);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password);
        curl_setopt($ch, CURLOPT_URL, $fileSyncUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpcode == 200)
        {
            try
            {
                $response = json_decode($server_output, true);
                if ($response === null && json_last_error() !== JSON_ERROR_NONE) {
                    $response = array(
                        'response_code'=>'02',
                        'response_text'=>'Respon tidak sesuai spesifikasi'
                    );
                }
            }
            catch(Exception $e)
            {
                $response = array(
                    'response_code'=>'02',
                    'response_text'=>'Respon tidak sesuai spesifikasi'
                );
            }
            return $response;
        }
        else
        {
            return array(
                'response_code'=>'01',
                'response_text'=>'Server tidak ditemukan'
            );
        }
    }
}

class PingException extends Exception
{
    private $previous;   
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code);  
        if (!is_null($previous))
        {
            $this -> previous = $previous;
        }
    }
    
}
if(@$_GET['action'] == 'ping')
{
    $ping = new SyncPing;
    $response = new stdClass;
    $success = false;
    if(isset($_POST['test']))
    {
        $fileSyncUrl2 = trim(@$_POST['url']);
        $username2 = trim(@$_POST['username']);
        $password2 = trim(@$_POST['password']);
        if($password2 == "")
        {
            $password2 = $password;
        }
    }
    else
    {
        $fileSyncUrl2 = $fileSyncUrl;
        $username2 = $username;
        $password2 = $password;
    }
    try
    {
        $response = $ping->ping($fileSyncUrl2, $username2, $password2);
        $success = $response['response_code'] == '00';
    }
    catch(PingException $e)
    {
        // Do nothing
    }

    header('Content-type: application/json'); //NOSONAR
    echo json_encode(
        array(
            'success'=>$success,
            'response'=>$response
        )
    );
    exit();
}

if(@$_GET['type'] == 'file')
{
    include_once dirname(__FILE__)."/lib/SyncFile.php";
    if(@$_GET['direction'] == 'down')
    {
        $fileSyncDownload = new \FileSyncDownload($database, $applicationRoot, $fileUploadBaseDir, $fileDownloadBaseDir, $filePoolBaseDir, $filePoolName, $filePoolRollingPrefix, $filePoolExtension);
        $fileSyncDownload->setUseRelativePath($configs->sync_file_use_relative_path);
        if(@$_GET['step'] == '1')
        {
            $success = $fileSyncDownload->fileDownloadInformation($fileSyncUrl, $username, $password);
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '2')
        {
            $recordList = $fileSyncDownload->filePrepareDownloadSyncFiles();
            $success = true;
            $completed = true;
            $recordList2 = array();
            foreach($recordList as $record)
            {
                $recordList2[] = array(
                    'recordId'=>$record['sync_file_id'],
                    'executed'=>false
                );
            }
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed,
                    'recordList'=>$recordList2
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '3')
        {
            $recordId = addslashes(trim(@$_GET['recordId']));
            $fileSyncDownload->fileDownloadSyncFiles($recordId, $permission, $fileSyncUrl, $username, $password);
            $success = true;
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '4')
        {
            $recordList = $fileSyncDownload->filePrepareDownloadUserFiles();
            $success = true;
            $completed = true;
            $recordList2 = array();
            foreach($recordList as $record)
            {
                $recordList2[] = array(
                    'recordId'=>$record['sync_file_id'],
                    'executed'=>false
                );
            }

            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed,
                    'recordList'=>$recordList2
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '5')
        {
            $recordId = trim(@$_GET['recordId']);
            $fileSyncDownload->fileDownloadUserFiles($recordId, $permission, $fileSyncUrl, $username, $password);
            $success = true;
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
    }

    if(@$_GET['direction'] == 'up')
    {
        $fileSyncUpload = new \FileSyncUpload($database, $applicationRoot, $fileUploadBaseDir, $fileDownloadBaseDir, $filePoolBaseDir, $filePoolName, $filePoolRollingPrefix, $filePoolExtension);
        $fileSyncUpload->setUseRelativePath($configs->sync_file_use_relative_path);
        if(@$_GET['step'] == '1')
        {
            $fileSyncUpload->fileUploadPreparation();
            $success = true;
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '2')
        {
            $recordList = $fileSyncUpload->filePrepareUploadUserFiles();
            $success = true;
            $completed = true;
            $recordList2 = array();
            foreach($recordList as $record)
            {
                $recordList2[] = array(
                    'recordId'=>$record['sync_file_id'],
                    'executed'=>false
                );
            }
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed,
                    'recordList'=>$recordList2
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '3')
        {
            $recordId = trim(@$_GET['recordId']);
            $fileSyncUpload->fileUploadUserFiles($recordId, $fileSyncUrl, $username, $password);
            $success = true;
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '4')
        {
            $recordList = $fileSyncUpload->filePrepareUploadSyncFiles();
            $success = true;
            $completed = true;
            $recordList2 = array();
            foreach($recordList as $record)
            {
                $recordList2[] = array(
                    'recordId'=>$record['sync_file_id'],
                    'executed'=>false
                );
            }
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed,
                    'recordList'=>$recordList2
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '5')
        {
            $recordId = trim(@$_GET['recordId']);
            $fileSyncUpload->fileUploadSyncFiles($recordId, $fileSyncUrl, $username, $password);
            $success = true;
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '6')
        {
            $success = true;
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
    }
}

if(@$_GET['type'] == 'database')
{
    include_once dirname(__FILE__)."/lib/SyncDatabase.php";
    if(@$_GET['direction'] == 'down')
    {
        $databaseSyncDownload = new \DatabaseSyncDownload($database, $applicationRoot, $databaseUploadBaseDir, $databaseDownloadBaseDir, $databasePoolBaseDir, $databasePoolName, $databasePoolRollingPrefix, $databasePoolExtension);
        if(@$_GET['step'] == '1')
        {
            $success = $databaseSyncDownload->databaseDownloadInformation($databaseSyncUrl, $username, $password);
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '2')
        {
            $recordList = $databaseSyncDownload->databasePrepareDownloadSyncFiles();
            $success = true;
            $completed = true;
            $recordList2 = array();
            foreach($recordList as $record)
            {
                $recordList2[] = array(
                    'recordId'=>$record['sync_database_id'],
                    'executed'=>false
                );
            }
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed,
                    'recordList'=>$recordList2
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '3')
        {
            $recordId = trim(@$_GET['recordId']);
            $success = $databaseSyncDownload->databaseDownloadSyncFiles($recordId, $permission, $databaseSyncUrl, $username, $password);
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '4')
        {
            $recordList = $databaseSyncDownload->databasePrepareExecuteQuery();
            $success = true;
            $completed = true;
            $recordList2 = array();
            foreach($recordList as $record)
            {
                $recordList2[] = array(
                    'recordId'=>$record['sync_database_id'],
                    'executed'=>false
                );
            }
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed,
                    'recordList'=>$recordList2
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '5')
        {
            $recordId = trim(@$_GET['recordId']);
            $success = $databaseSyncDownload->databaseExecuteQuery($recordId);
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
    }
    if(@$_GET['direction'] == 'up')
    {
        $databaseSyncUpload = new \DatabaseSyncUpload($database, $applicationRoot, $databaseUploadBaseDir, $databaseDownloadBaseDir, $databasePoolBaseDir, $databasePoolName, $databasePoolRollingPrefix, $databasePoolExtension);
        if(@$_GET['step'] == '1')
        {
            $success = $databaseSyncUpload->syncLocalQueryToDatabase();
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '2')
        {
            $recordList = $databaseSyncUpload->databasePrepareUploadSyncFiles();
            $success = true;
            $completed = true;
            $recordList2 = array();
            foreach($recordList as $record)
            {
                $recordList2[] = array(
                    'recordId'=>$record['sync_database_id'],
                    'executed'=>false
                );
            }
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed,
                    'recordList'=>$recordList2
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '3')
        {
            $recordId = trim(@$_GET['recordId']);         
            $success = $databaseSyncUpload->databaseUploadSyncFiles($recordId, $databaseSyncUrl, $username, $password);
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '4')
        {
            $success = true;
            $completed = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$completed
                )
            );
            exit();
        }
    }
}



