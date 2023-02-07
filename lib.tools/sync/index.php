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
    if(@$_GET['direction'] == 'up')
    {
        $fileSyncUpload = new \FileSyncUpload($database, $applicationRoot, $fileUploadBaseDir, $fileDownloadBaseDir, $filePoolBaseDir, $filePoolName, $filePoolRollingPrefix, $filePoolExtension);
        if(@$_GET['step'] == '1')
        {
            $fileSyncUpload->syncLocalUserFileToDatabase();
        }
        else if(@$_GET['step'] == '2')
        {
            $fileSyncUpload->syncLocalUserFileToSyncHub($fileSyncUrl, $username, $password);
        }
    }
    if(@$_GET['direction'] == 'down')
    {
        $fileSyncDownload = new \FileSyncDownload($database, $applicationRoot, $fileUploadBaseDir, $fileDownloadBaseDir, $filePoolBaseDir, $filePoolName, $filePoolRollingPrefix, $filePoolExtension);
        if(@$_GET['step'] == '1')
        {
            $fileSyncDownload->syncHubToDatabase($fileSyncUrl, $username, $password);
        }
        else if(@$_GET['step'] == '2')
        {
            $fileSyncDownload->syncRemoteUserFileLocalHost($permission, $fileSyncUrl, $username, $password);
        }
    }
}

if(@$_GET['type'] == 'database')
{
    include_once dirname(__FILE__)."/lib/SyncDatabase.php";
    if(@$_GET['direction'] == 'up')
    {
        $databaseSyncUpload = new \DatabaseSyncUpload($database, $applicationRoot, $databaseUploadBaseDir, $databaseDownloadBaseDir, $databasePoolBaseDir, $databasePoolName, $databasePoolRollingPrefix, $databasePoolExtension);
        if(@$_GET['step'] == '1')
        {
            $databaseSyncUpload->syncLocalQueryToDatabase();
        }
        else if(@$_GET['step'] == '2')
        {
            $databaseSyncUpload->syncLocalQueryToRemoteHost($databaseSyncUrl, $username, $password);
        }
    }
    if(@$_GET['direction'] == 'down')
    {
        $databaseSyncDownload = new \DatabaseSyncDownload($database, $applicationRoot, $databaseUploadBaseDir, $databaseDownloadBaseDir, $databasePoolBaseDir, $databasePoolName, $databasePoolRollingPrefix, $databasePoolExtension);
        if(@$_GET['step'] == '1')
        {
            $databaseSyncDownload->syncRemoteHostRecordToDatabase($databaseSyncUrl, $username, $password);
        }
        else if(@$_GET['step'] == '2')
        {
            $databaseSyncDownload->syncRemoteQueryToDatabase();
        }
    }
}



