let fileSyncFilesDownload = {};
let fileUserFilesDownload = {};
let fileSyncFilesUpload = {};
let fileUserFilesUpload = {};

let databaseSyncFilesDownload = {};
let databaseUserFilesDownload = {};
let databaseSyncFilesUpload = {};
let databaseUserFilesUpload = {};

function startSync()
{
    fileDownloadInformation();
}

function getFileSyncId(source)
{
    if(source.recordList.length > 0)
    {
        for(let i in source.recordList)
        {
            if(!source.recordList[i].executed)
            {
                return source.recordList[i].recordId;
            }
        }
    }
    return null;
}

function updateRecordStatus(source, recordId)
{
    if(source.recordList.length > 0)
    {
        for(let i in source.recordList)
        {
            if(source.recordList[i].recordId == recordId)
            {
                source.recordList[i].executed = true;
            }
        }
    }
}

function updateProgressBar(type, direction, step, value)
{
    let percent = 0;
    if(typeof value == 'object')
    {
        let list = value;
        let all = list.length;
        let val = 0;
        for(let i in list)
        {
            if(list[i].executed)
            {
                val++;
            }
        }
        percent = all > 0 ? 100*val/all : 100;
    }
    else
    {
        percent = value;
    }
    let selector = '.sync-item[data-type="'+type+'"][data-direction="'+direction+'"][data-step="'+step+'"]';
    let pb = $(selector).find('.progress-bar');     
    pb.css({'width':percent+'%'});
    pb.attr('aria-valuenow', percent);
}

/**
 * Download informasi dari sync hub
 */
function fileDownloadInformation()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'down',
            step:1
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                if(response.completed)
                {
                    filePrepareDownloadSyncFiles();
                    updateProgressBar('file', 'down', 1, 100);
                }
            }
        }
    });
}

/**
 * Persiapan download file sync dari sync hub
 */
function filePrepareDownloadSyncFiles()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'down',
            step:2
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            
            if(response.success)
            {
                fileSyncFilesDownload.recordList = response.recordList;
                let recordId = getFileSyncId(fileSyncFilesDownload);
                if(recordId != null)
                {
                    fileDownloadSyncFiles(recordId);                         
                }
            }
        }
    });
}

/**
 * Download file sync dari sync hub
 */
function fileDownloadSyncFiles(recordId)
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'down',
            step:3,
            recordId:recordId
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateRecordStatus(fileSyncFilesDownload, recordId);
                updateProgressBar('file', 'down', 3, fileSyncFilesDownload.recordList);
                let nextRecordId = getFileSyncId(fileSyncFilesDownload);
                if(nextRecordId != null)
                {
                    fileDownloadSyncFiles(nextRecordId);
                }
                else
                {
                    filePrepareDownloadUserFiles();
                }
            }
        }
    });
}

/**
 * Persiapan download file pengguna dari sync hub
 */
function filePrepareDownloadUserFiles()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'down',
            step:4
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            
            if(response.success)
            {
                fileUserFilesDownload.recordList = response.recordList;
                let recordId = getFileSyncId(fileUserFilesDownload);
                if(recordId != null)
                {
                    fileDownloadUserFiles(recordId);                         
                }
            }
        }
    });
}

/**
 * Download file pengguna dari sync hub
 */
function fileDownloadUserFiles(recordId)
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'down',
            step:5,
            recordId:recordId
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateRecordStatus(fileUserFilesDownload, recordId);
                updateProgressBar('file', 'down', 5, fileUserFilesDownload.recordList);
                let nextRecordId = getFileSyncId(fileUserFilesDownload);
                if(nextRecordId != null)
                {
                    fileDownloadUserFiles(nextRecordId);
                }
                else
                {
                    filePrepareUploadUserFiles();
                }
            }
        }
    });
}















/**
 * Persiapan upload file pengguna ke sync hub
 */
function filePrepareUploadUserFiles()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'up',
            step:2
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                fileSyncFilesUpload.recordList = response.recordList;
                let recordId = getFileSyncId(fileSyncFilesUpload);
                if(recordId != null)
                {
                    fileUploadFiles(recordId);                         
                }
            }
        }
    });
}

/**
 * Download file sync dari sync hub
 */
function fileUploadFiles(recordId)
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'up',
            step:3,
            recordId:recordId
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateRecordStatus(fileSyncFilesUpload, recordId);
                updateProgressBar('file', 'up', 1, fileSyncFilesUpload.recordList);
                let nextRecordId = getFileSyncId(fileSyncFilesUpload);
                if(nextRecordId != null)
                {
                    fileUploadFiles(nextRecordId);
                }
                else
                {
                    filePrepareUploadSyncFiles();
                }
            }
        }
    });
}


/**
 * Persiapan upload file sync ke sync hub
 */
function filePrepareUploadSyncFiles()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'up',
            step:4
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            
            if(response.success)
            {
                fileUserFilesUpload.recordList = response.recordList;
                let recordId = getFileSyncId(fileUserFilesUpload);
                if(recordId != null)
                {
                    fileUploadSyncFiles(recordId);                         
                }
            }
        }
    });
}

/**
 * Upload file sync ke sync hub
 */
function fileUploadSyncFiles(recordId)
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'up',
            step:5,
            recordId:recordId
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateRecordStatus(fileUserFilesUpload, recordId);
                updateProgressBar('file', 'up', 3, fileUserFilesUpload.recordList);
                let nextRecordId = getFileSyncId(fileUserFilesUpload);
                if(nextRecordId != null)
                {
                    fileUploadSyncFiles(nextRecordId);
                }
                else
                {
                    fileUploadInformation();
                }
            }
        }
    });
}

/**
 * Upload informasi ke sync hub
 */
function fileUploadInformation()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'file',
            direction:'up',
            step:6
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateProgressBar('file', 'up', 5, 100);

                databaseDownloadInformation();
            }
        }
    });
}















/**
 * Download informasi dari sync hub
 */
function databaseDownloadInformation()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'database',
            direction:'down',
            step:1
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                if(response.completed)
                {
                    databasePrepareDownloadSyncFiles();
                    updateProgressBar('database', 'down', 1, 100);
                }
            }
        }
    });
}

/**
 * Persiapan download database sync dari sync hub
 */
function databasePrepareDownloadSyncFiles()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'database',
            direction:'down',
            step:2
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            
            if(response.success)
            {
                databaseSyncFilesDownload.recordList = response.recordList;
                let recordId = getFileSyncId(databaseSyncFilesDownload);
                if(recordId != null)
                {
                    databaseDownloadSyncFiles(recordId);                         
                }
            }
        }
    });
}

/**
 * Download database sync dari sync hub
 */
function databaseDownloadSyncFiles(recordId)
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'database',
            direction:'down',
            step:3,
            recordId:recordId
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateRecordStatus(databaseSyncFilesDownload, recordId);
                updateProgressBar('database', 'down', 3, databaseSyncFilesDownload.recordList);
                let nextRecordId = getFileSyncId(databaseSyncFilesDownload);
                if(nextRecordId != null)
                {
                    databaseDownloadSyncFiles(nextRecordId);
                }
                else
                {
                    databasePrepareExecuteQuery();
                }
            }
        }
    });
}


/**
 * Persiapan eksekusi query
 */
function databasePrepareExecuteQuery()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'database',
            direction:'down',
            step:4
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            
            if(response.success)
            {
                databaseSyncFilesDownload.recordList = response.recordList;
                let recordId = getFileSyncId(databaseSyncFilesDownload);
                if(recordId != null)
                {
                    databaseExecuteQuery(recordId);                         
                }
            }
        }
    });
}

/**
 * Download database sync dari sync hub
 */
function databaseExecuteQuery(recordId)
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'database',
            direction:'down',
            step:5,
            recordId:recordId
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateRecordStatus(databaseSyncFilesDownload, recordId);
                updateProgressBar('database', 'down', 5, databaseSyncFilesDownload.recordList);
                let nextRecordId = getFileSyncId(databaseSyncFilesDownload);
                if(nextRecordId != null)
                {
                    databaseExecuteQuery(nextRecordId);
                }
                else
                {
                    databasePrepareUploadSyncFiles();
                }
            }
        }
    });
}

/**
 * Persiapan upload database sync ke sync hub
 */
function databasePrepareUploadSyncFiles()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'database',
            direction:'up',
            step:4
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            
            if(response.success)
            {
                databaseUserFilesUpload.recordList = response.recordList;
                let recordId = getFileSyncId(databaseUserFilesUpload);
                if(recordId != null)
                {
                    databaseUploadSyncFiles(recordId);                         
                }
            }
        }
    });
}

/**
 * Upload database sync ke sync hub
 */
function databaseUploadSyncFiles(recordId)
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'database',
            direction:'up',
            step:5,
            recordId:recordId
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateRecordStatus(databaseUserFilesUpload, recordId);
                updateProgressBar('database', 'up', 3, databaseUserFilesUpload.recordList);
                let nextRecordId = getFileSyncId(databaseUserFilesUpload);
                if(nextRecordId != null)
                {
                    databaseUploadSyncFiles(nextRecordId);
                }
                else
                {
                    databaseUploadInformation();
                }
            }
        }
    });
}

/**
 * Upload informasi ke sync hub
 */
function databaseUploadInformation()
{
    $.ajax({
        url:'lib.tools/sync/test.php',
        data:{
            type:'database',
            direction:'up',
            step:6
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {
            if(response.success)
            {
                updateProgressBar('database', 'up', 5, 100);

            }
        }
    });
}
