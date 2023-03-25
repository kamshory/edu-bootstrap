<?php
include_once dirname(dirname(__DIR__))."/lib.inc/auth-sync.php";
if(!$syncConfigs->sync_data_enable)
{
    exit();
}

$application_code = $memberLoggedIn->school_id;

if(@$_GET['type'] == 'file' 
|| @$_GET['type'] == 'database' 
|| @$_GET['action'] == 'ping'
|| @$_GET['action'] == 'sync-time'
)
{
    $syncHubURL = $database->getSystemVariable("sync_hub_url");
    $fileSyncUrl = rtrim($syncHubURL, "/")."/";
    $databaseSyncUrl = rtrim($syncHubURL, "/")."/";
    $username = $database->getSystemVariable("sync_hub_username");
    $password = $database->getSystemVariable("sync_hub_password");   
}


if(@$_GET['action'] == 'ping')
{
    $ping = new \Sync\SyncPing($application_code);
    $response = new \stdClass;
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
        $success = $response['response_code'] == \Sync\SyncResponseCode::SUCCESS;
    }
    catch(\Sync\SyncException $e)
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
if(@$_GET['action'] == 'sync-time')
{
    $response = new \stdClass;
    $success = false;

    if($syncConfigs->sync_time_enable)
    {
        $syncTime = new \Sync\SyncTime($application_code);

        $fileSyncUrl2 = $fileSyncUrl;
        $username2 = $username;
        $password2 = $password;

        try
        {
            $response = $syncTime->syncTime($fileSyncUrl2, $username2, $password2, $database);
            $success = $response['response_code'] == \Sync\SyncResponseCode::SUCCESS;
        }
        catch(\Sync\SyncException $e)
        {
            // Do nothing
        }
    }
    else
    {
        $response->response_code = \Sync\SyncResponseCode::SUCCESS;
        $response->response_text = \Sync\SyncResponseCode::getResponseText($response->response_code);
        $success = $response->response_code == \Sync\SyncResponseCode::SUCCESS;
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
    header('Content-type: application/json'); //NOSONAR
    if(@$_GET['direction'] == 'down')
    {
        $fileSyncDownload = new \Sync\FileSyncDownload($database, $applicationRoot, $fileUploadBaseDir, $fileDownloadBaseDir, $filePoolBaseDir, $filePoolName, $filePoolRollingPrefix, $filePoolExtension);
        $fileSyncDownload->setApplication($application_code);
        $fileSyncDownload->setUseRelativePath($syncConfigs->sync_file_use_relative_path);
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
        $fileSyncUpload = new \Sync\FileSyncUpload($database, $applicationRoot, $fileUploadBaseDir, $fileDownloadBaseDir, $filePoolBaseDir, $filePoolName, $filePoolRollingPrefix, $filePoolExtension);
        $fileSyncUpload->setApplication($application_code);
        $fileSyncUpload->setUseRelativePath($syncConfigs->sync_file_use_relative_path);
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
    header('Content-type: application/json'); //NOSONAR
    if(@$_GET['direction'] == 'down')
    {
        $databaseSyncDownload = new \Sync\DatabaseSyncDownload($database, $applicationRoot, $databaseUploadBaseDir, $databaseDownloadBaseDir, $databasePoolBaseDir, $databasePoolName, $databasePoolRollingPrefix, $databasePoolExtension);
        $databaseSyncDownload->setApplication($application_code);
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
        $databaseSyncUpload = new \Sync\DatabaseSyncUpload($database, $applicationRoot, $databaseUploadBaseDir, $databaseDownloadBaseDir, $databasePoolBaseDir, $databasePoolName, $databasePoolRollingPrefix, $databasePoolExtension);
        $databaseSyncUpload->setApplication($application_code);
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
