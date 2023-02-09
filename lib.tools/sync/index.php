<?php
include_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-sync.php";

if(@$_GET['type'] == 'file' || @$_GET['type'] == 'database')
{

    $applicationRoot = dirname(dirname(dirname(__FILE__)));
    $username = 'user';
    $password = 'password';
    $permission = 0755;
    
    $fileUploadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/upload";
    $fileDownloadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/upload";
    $filePoolBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/pool";
    $filePoolName = "pool";
    $filePoolRollingPrefix = "pool_";
    $filePoolExtension = ".txt";
    $fileSyncUrl = 'http://localhost/sync/file/';

    $databaseUploadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/database/upload";
    $databaseDownloadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/database/upload";
    $databasePoolBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/database/pool";
    $databasePoolName = "pool";
    $databasePoolRollingPrefix = "pool_";
    $databasePoolExtension = ".txt";
    $databaseSyncUrl = 'http://localhost/sync/database/';
    
}


if(@$_GET['type'] == 'file')
{
    include_once dirname(__FILE__)."/lib/SyncFile.php";
    if(@$_GET['direction'] == 'down')
    {
        $fileSyncDownload = new \FileSyncDownload($database, $applicationRoot, $fileUploadBaseDir, $fileDownloadBaseDir, $filePoolBaseDir, $filePoolName, $filePoolRollingPrefix, $filePoolExtension);
        if(@$_GET['step'] == '1')
        {
            $success = $fileSyncDownload->fileDownloadInformation($fileSyncUrl, $username, $password);
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '2')
        {
            $recordList = $fileSyncDownload->filePrepareDownloadSyncFiles();
            $success = true;
            $completed = $success;
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
        }
        else if(@$_GET['step'] == '4')
        {
            $recordList = $fileSyncDownload->filePrepareDownloadUserFiles();
            $success = true;
            $completed = $success;
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
        }
    }
    if(@$_GET['direction'] == 'up')
    {
        $success = $fileSyncUpload = new \FileSyncUpload($database, $applicationRoot, $fileUploadBaseDir, $fileDownloadBaseDir, $filePoolBaseDir, $filePoolName, $filePoolRollingPrefix, $filePoolExtension);
        if(@$_GET['step'] == '1')
        {
            $fileSyncUpload->fileUploadPreparation();
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '2')
        {
            $recordList = $fileSyncUpload->filePrepareUploadUserFiles();
            $success = !empty($recordList);
            $completed = $success;
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
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '4')
        {
            $recordList = $fileSyncUpload->filePrepareUploadSyncFiles();

            $success = true;
            $completed = $success;
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
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '6')
        {
            $success = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
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
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '2')
        {
            $recordList = $databaseSyncDownload->databasePrepareDownloadSyncFiles();
            $success = true;
            $completed = $success;
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
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '4')
        {
            $recordList = $databaseSyncDownload->databasePrepareExecuteQuery();
            $success = true;
            $completed = $success;
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
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
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
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '2')
        {
            $recordList = $databaseSyncUpload->databasePrepareUploadSyncFiles();
            $success = true;
            $completed = $success;
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
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
        else if(@$_GET['step'] == '4')
        {
            $success = true;
            echo json_encode(
                array(
                    'success'=>$success,
                    'completed'=>$success
                )
            );
            exit();
        }
    }
}



