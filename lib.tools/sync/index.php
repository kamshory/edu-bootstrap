<?php
include_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-sync.php";

if(@$_GET['type'] == 'file' || @$_GET['type'] == 'database')
{
    $applicationRoot = dirname(dirname(dirname(__FILE__)));
    $permission = 0755;

    $syncHubURL = "http://localhost/sync/";
    $syncHubURL = $database->getSystemVariable("sync_hub_url");

    $username = 'user';
    $username = $database->getSystemVariable("sync_hub_username");

    $password = 'password';
    $password = $database->getSystemVariable("sync_hub_password");
    
    $fileUploadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/upload";
    $fileDownloadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/download";
    $filePoolBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/pool";
    $filePoolName = "pool";
    $filePoolRollingPrefix = "pool_";
    $filePoolExtension = ".txt";
    $fileSyncUrl = rtrim($syncHubURL, "/")."/";

    $databaseUploadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/database/upload";
    $databaseDownloadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/database/download";
    $databasePoolBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/database/pool";
    $databasePoolName = "pool";
    $databasePoolRollingPrefix = "pool_";
    $databasePoolExtension = ".txt";
    $databaseSyncUrl = rtrim($syncHubURL, "/")."/";
    
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



