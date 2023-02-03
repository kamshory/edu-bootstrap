<?php
include_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-sync.php";

if(@$_GET['type'] == 'file' || @$_GET['type'] == 'database')
{
    $uploadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/upload";
    $downloadBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/upload";
    $poolBaseDir = dirname(dirname(dirname(__FILE__)))."/lib.sync/file/pool";
    $poolFileName = "pool";
    $poolRollingPrefix = "pool_";
    $poolFileExtension = ".txt";

    $url = 'http://192.168.1.2/sync';
    $username = 'user';
    $password = 'password';
    $permission = 0755;
    
}
if(@$_GET['type'] == 'file')
{
    

    include_once dirname(__FILE__)."/lib/SyncFile.php";
    if(@$_GET['direction'] == 'up')
    {
        $syncUpload = new \FileSyncUpload($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension);
        if(@$_GET['step'] == '1')
        {
            $syncUpload->syncLocalUserFileToDatabase();
        }
        else if(@$_GET['step'] == '2')
        {
            $syncUpload->syncLocalUserFileToRemoteHost($url, $username, $password);
        }
    }
    if(@$_GET['direction'] == 'down')
    {
        $syncDownload = new \FileSyncDownload($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension);
        if(@$_GET['step'] == '1')
        {
            $syncDownload->syncRemoteHostRecordToDatabase($url, $username, $password);
        }
        else if(@$_GET['step'] == '2')
        {
            $syncDownload->syncRemoteUserFileLocalHost($permission, $url, $username, $password);
        }
    }
}


if(@$_GET['type'] == 'database')
{
    include_once dirname(__FILE__)."/lib/SyncDatabase.php";
    if(@$_GET['direction'] == 'up')
    {
        $syncUpload = new \DatabaseSyncUpload($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension);
        if(@$_GET['step'] == '1')
        {
            $syncUpload->syncLocalQueryToDatabase();
        }
        else if(@$_GET['step'] == '2')
        {
            $syncUpload->syncLocalQueryToRemoteHost($url, $username, $password);
        }
    }
    if(@$_GET['direction'] == 'down')
    {
        $syncDownload = new \DatabaseSyncDownload($database, $uploadBaseDir, $downloadBaseDir, $poolBaseDir, $poolFileName, $poolRollingPrefix, $poolFileExtension);
        if(@$_GET['step'] == '1')
        {
            $syncDownload->syncRemoteHostRecordToDatabase($url, $username, $password);
        }
        else if(@$_GET['step'] == '2')
        {
            $syncDownload->syncRemoteQueryToDatabase();
        }
    }
}



