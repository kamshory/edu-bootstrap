let fileSyncFilesDownload = {};
let fileUserFilesDownload = {};
let fileSyncFilesUpload = {};
let fileUserFilesUpload = {};

let databaseSyncFilesDownload = {};
let databaseUserFilesDownload = {};
let databaseSyncFilesUpload = {};
let syncPath = '';
function startSync(path, clbk)
{
    syncPath = path;
    fileDownloadInformation(clbk);
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
function fileDownloadInformation(clbk)
{
    $.ajax({
        url:syncPath,
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
                    filePrepareDownloadSyncFiles(clbk);
                    updateProgressBar('file', 'down', 1, 100);
                }
            }
        }
    });
}

/**
 * Persiapan download file sync dari sync hub
 */
function filePrepareDownloadSyncFiles(clbk)
{
    $.ajax({
        url:syncPath,
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
                    fileDownloadSyncFiles(recordId, clbk);                         
                }
                else
                {
                    updateProgressBar('file', 'down', 3, 100);
                    filePrepareDownloadUserFiles(clbk);
                }
            }
        }
    });
}

/**
 * Download file sync dari sync hub
 */
function fileDownloadSyncFiles(recordId, clbk)
{
    $.ajax({
        url:syncPath,
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
                    fileDownloadSyncFiles(nextRecordId, clbk);
                }
                else
                {
                    filePrepareDownloadUserFiles(clbk);
                }
            }
        }
    });
}

/**
 * Persiapan download file pengguna dari sync hub
 */
function filePrepareDownloadUserFiles(clbk)
{
    $.ajax({
        url:syncPath,
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
                    fileDownloadUserFiles(recordId, clbk);                         
                }
                else
                {
                    updateProgressBar('file', 'down', 5, 100);
                    fileUploadPreparation(clbk);
                }
            }
        }
    });
}

/**
 * Download file pengguna dari sync hub
 */
function fileDownloadUserFiles(recordId, clbk)
{
    $.ajax({
        url:syncPath,
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
                    fileDownloadUserFiles(nextRecordId, clbk);
                }
                else
                {
                    fileUploadPreparation(clbk);
                }
            }
        }
    });
}

function fileUploadPreparation(clbk)
{
    $.ajax({
        url:syncPath,
        data:{
            type:'file',
            direction:'up',
            step:1
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {         
            if(response.success)
            {             
                updateProgressBar('file', 'up', 1, 100);
                filePrepareUploadUserFiles(clbk);
            }
        }
    });
}


/**
 * Persiapan upload file pengguna ke sync hub
 */
function filePrepareUploadUserFiles(clbk)
{
    $.ajax({
        url:syncPath,
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
                    fileUploadUserFiles(recordId, clbk);                         
                }
                else
                {
                    updateProgressBar('file', 'up', 3, 100);
                    filePrepareUploadSyncFiles(clbk);
                }
            }
        }
    });
}

/**
 * Download file sync dari sync hub
 */
function fileUploadUserFiles(recordId, clbk)
{
    $.ajax({
        url:syncPath,
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
                updateProgressBar('file', 'up', 3, fileSyncFilesUpload.recordList);
                let nextRecordId = getFileSyncId(fileSyncFilesUpload);
                if(nextRecordId != null)
                {
                    fileUploadUserFiles(nextRecordId, clbk);
                }
                else
                {
                    filePrepareUploadSyncFiles(clbk);
                }
            }
        }
    });
}


/**
 * Persiapan upload file sync ke sync hub
 */
function filePrepareUploadSyncFiles(clbk)
{
    $.ajax({
        url:syncPath,
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
                    fileUploadSyncFiles(recordId, clbk);                         
                }
                else{
                    updateProgressBar('file', 'up', 5, 100);
                    fileUploadInformation(clbk);
                }
            }
        }
    });
}

/**
 * Upload file sync ke sync hub
 */
function fileUploadSyncFiles(recordId, clbk)
{
    $.ajax({
        url:syncPath,
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
                updateProgressBar('file', 'up', 5, fileUserFilesUpload.recordList);
                let nextRecordId = getFileSyncId(fileUserFilesUpload);
                if(nextRecordId != null)
                {
                    fileUploadSyncFiles(nextRecordId, clbk);
                }
                else
                {
                    fileUploadInformation(clbk);
                }
            }
        }
    });
}

/**
 * Upload informasi ke sync hub
 */
function fileUploadInformation(clbk)
{
    $.ajax({
        url:syncPath,
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
                updateProgressBar('file', 'up', 7, 100);

                databaseDownloadInformation(clbk);
            }
        }
    });
}















/**
 * Download informasi dari sync hub
 */
function databaseDownloadInformation(clbk)
{
    $.ajax({
        url:syncPath,
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
                    databasePrepareDownloadSyncFiles(clbk);
                    updateProgressBar('database', 'down', 1, 100);
                }
            }
        }
    });
}

/**
 * Persiapan download database sync dari sync hub
 */
function databasePrepareDownloadSyncFiles(clbk)
{
    $.ajax({
        url:syncPath,
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
                let recordId = getFileSyncId(databaseSyncFilesDownload, clbk);
                console.log(recordId)
                if(recordId != null)
                {
                    databaseDownloadSyncFiles(recordId, clbk);                         
                }
                else
                {
                    updateProgressBar('database', 'down', 3, 100);
                    console.log('updateProgressBar')
                    databasePrepareExecuteQuery(clbk);
                }
            }
        }
    });
}

/**
 * Download database sync dari sync hub
 */
function databaseDownloadSyncFiles(recordId, clbk)
{
    $.ajax({
        url:syncPath,
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
                    databaseDownloadSyncFiles(nextRecordId, clbk);
                }
                else
                {
                    databasePrepareExecuteQuery(clbk);
                }
            }
        }
    });
}


/**
 * Persiapan eksekusi query
 */
function databasePrepareExecuteQuery(clbk)
{
    $.ajax({
        url:syncPath,
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
                    databaseExecuteQuery(recordId, clbk);                         
                }
                else
                {
                    updateProgressBar('database', 'down', 5, 100);
                    databaseUploadPreparation(clbk);
                }
            }
        }
    });
}

/**
 * Download database sync dari sync hub
 */
function databaseExecuteQuery(recordId, clbk)
{
    $.ajax({
        url:syncPath,
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
                    databaseExecuteQuery(nextRecordId, clbk);
                }
                else
                {
                    databaseUploadPreparation(clbk);
                }
            }
        }
    });
}

function databaseUploadPreparation(clbk)
{
    $.ajax({
        url:syncPath,
        data:{
            type:'database',
            direction:'up',
            step:1
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {         
            if(response.success)
            {             
                updateProgressBar('database', 'up', 1, 100);
                databasePrepareUploadSyncFiles(clbk);
            }
        }
    });
}

/**
 * Persiapan upload database sync ke sync hub
 */
function databasePrepareUploadSyncFiles(clbk)
{
    $.ajax({
        url:syncPath,
        data:{
            type:'database',
            direction:'up',
            step:2
        },
        type:'GET',
        dataType:'json',
        success:function(response)
        {         
            if(response.success)
            {
                databaseSyncFilesUpload.recordList = response.recordList;
                let recordId = getFileSyncId(databaseSyncFilesUpload);
                if(recordId != null)
                {
                    databaseUploadSyncFiles(recordId, clbk);                         
                }
                else
                {
                    updateProgressBar('database', 'up', 3, 100);
                    databaseUploadInformation(clbk);
                }
            }
        }
    });
}

/**
 * Upload database sync ke sync hub
 */
function databaseUploadSyncFiles(recordId, clbk)
{
    $.ajax({
        url:syncPath,
        data:{
            type:'database',
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
                updateRecordStatus(databaseSyncFilesUpload, recordId);
                updateProgressBar('database', 'up', 3, databaseSyncFilesUpload.recordList);
                let nextRecordId = getFileSyncId(databaseSyncFilesUpload);
                if(nextRecordId != null)
                {
                    databaseUploadSyncFiles(nextRecordId, clbk);
                }
                else
                {
                    databaseUploadInformation(clbk);
                }
            }
        }
    });
}

/**
 * Upload informasi ke sync hub
 */
function databaseUploadInformation(clbk)
{
    $.ajax({
        url:syncPath,
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
                updateProgressBar('database', 'up', 5, 100);
                clbk();
            }
        }
    });
}
