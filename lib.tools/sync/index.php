<?php
include_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth-sync.php";
if(@$_GET['type'] == 'file')
{
    include_once dirname(__FILE__)."/lib/SyncFile.php";
    if(@$_GET['direction'] == 'up')
    {
        $syncUpload = new \FileSyncUpload($database, '', '', '', '', '');
        if(@$_GET['step'] == '1')
        {
            $syncUpload->syncLocalUserFileToDatabase();
        }
        else if(@$_GET['step'] == '2')
        {
            $syncUpload->syncLocalUserFileToRemoteHost('', '', '');
        }
    }
    if(@$_GET['direction'] == 'down')
    {
        $syncDownload = new \FileSyncDownload($database, '', '', '', '', '');
        if(@$_GET['step'] == '1')
        {
            $syncDownload->syncRemoteHostRecordToDatabase('', '', '', '');
        }
        else if(@$_GET['step'] == '2')
        {
            $syncDownload->syncRemoteUserFileLocalHost('', '', '', '');
        }
    }
}


if(@$_GET['type'] == 'database')
{
    include_once dirname(__FILE__)."/lib/SyncDatabase.php";
    if(@$_GET['direction'] == 'up')
    {
        $syncUpload = new \DatabaseSyncUpload($database, '', '', '', '', '');
        if(@$_GET['step'] == '1')
        {
            $syncUpload->syncLocalQueryToDatabase();
        }
        else if(@$_GET['step'] == '2')
        {
            $syncUpload->syncLocalQueryToRemoteHost('', '', '');
        }
    }
    if(@$_GET['direction'] == 'down')
    {
        $syncDownload = new \DatabaseSyncDownload($database, '', '', '', '', '');
        if(@$_GET['step'] == '1')
        {
            $syncDownload->syncRemoteHostRecordToDatabase('', '', '', '');
        }
        else if(@$_GET['step'] == '2')
        {
            $syncDownload->syncRemoteQueryLocalHost('', '', '', '');
        }
    }
}



