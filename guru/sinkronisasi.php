<?php
require_once dirname(dirname(__FILE__)) . "/lib.inc/auth-admin.php";
$pageTitle = "Sinkronisasi Data";

if(@$_POST['action'] == 'save-config')
{
    $fileSyncUrl2 = trim(@$_POST['url']);
    $username2 = trim(@$_POST['username']);
    $password2 = trim(@$_POST['password']);

    if(!empty($fileSyncUrl2))
    {
        $database->setSystemVariable("sync_hub_url", addslashes($fileSyncUrl2));
    }

    if(!empty($username2))
    {
        $database->setSystemVariable("sync_hub_username", addslashes($username2));
    }

    if(!empty($password2))
    {
        $database->setSystemVariable("sync_hub_password", addslashes($password2));
    }

}

if(!empty($school_id)) 
{
    require_once dirname(__FILE__) . "/lib.inc/header.php"; //NOSONAR
    ?>
    <style>
        .sync-container{
            margin-bottom: 20px;
        }
        .sync-item{
            padding: 2px 0;
        }
        .sync-label{
            padding: 2px 0;
        }
        .sync-message{
            display:none;
        }
        .sync-area{
            display:none;
        }
    </style>

    <script>
        $(document).ready(function(){
            $(document).on('click', '#start-sync', function(e){
                doPing(
                {},
                function(response){
                    doSync();
                }, 
                function(response){
                    doAlert('Terjadi Kesalahan', response.response.response_text);
                });
                
            });

            $(document).on('click', '#test-ping', function(e){
                if($('#url').val() != '' && $('#username').val().trim() != '')
                {
                    doPing(
                    {
                        'test': true,
                        'url': $('#url').val().trim(),
                        'username': $('#username').val().trim(),
                        'password': $('#password').val().trim()
                    },
                    function(response){
                        doAlert('Informasi', response.response.response_text);
                    }, 
                    function(response){
                        doAlert('Terjadi Kesalahan', response.response.response_text);
                    });
                }
            });

            $(document).on('click', '#save-config', function(e){
                if($('#url').val() != '' && $('#url').val().trim() != '')
                {
                    let url = $('#url').val();
                    let username = $('#username').val();
                    let password = $('#password').val();
                    $.ajax({
                    url: 'sinkronisasi.php',
                    data: {action:'save-config', url:url, username:username, password:password},
                    type:'POST',
                    dataType:'text',
                    success:function(response)
                    {
                        $('#configModal').modal('hide');
                    }
                });
                }
            });
        });
        
        function doPing(args, clbkSuccess, clbkFailed)
        {
            args = args || {};
            console.log(args)
            $.ajax({
                    url: 'lib.tools/sync/?action=ping',
                    data: args,
                    type:'POST',
                    dataType:'json',
                    success:function(response)
                    {
                        console.log(response)
                        if(response.success)
                        {
                            clbkSuccess(response);
                        }
                        else
                        {
                            clbkFailed(response)
                        }
                    }
                });
        }
        

        function doAlert(title, message)
        {
            $('#alerModal .modal-title').text(title);
            $('#alerModal .modal-body').text(message);
            $('#alerModal').modal('show');
        }
        function doSync()
        {
            $('#start-sync')[0].disabled = true;
            $('.sync-area').slideDown('fast', function(e2){
                    
            });
            startSync('lib.tools/sync/', function(e3){
                $('.sync-message').slideDown('fast', function(e4){
                    $(".progress-bar").each(function(e5){
                        $(this).addClass('bg-success');
                        $(this).removeClass('bg-primary')
                    });
                });
            });
        }
    </script>

    <script type="text/javascript" src="<?php echo $cfg->base_url;?>lib.assets/script/sync.js"></script>
    
<!-- Modal -->
<?php
    $syncHubURL = $database->getSystemVariable("sync_hub_url");
    $fileSyncUrl = rtrim($syncHubURL, "/")."/";
    $username = $database->getSystemVariable("sync_hub_username");
?>
<div class="modal fade" id="configModal" tabindex="-1" role="dialog" aria-labelledby="configModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="configModalLabel">Konfigurasi Sync Hub</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table two-side-table responsive-tow-side-table" width="100%">
            <tr>
                <td>
                    URL Sync Hub
                </td>
                <td>
                    <input class="form-control" type="url" id="url" value="<?php echo htmlspecialchars($fileSyncUrl);?>">
                </td>
            </tr>
            <tr>
                <td>
                    Username
                </td>
                <td>
                    <input class="form-control" type="text" id="username" value="<?php echo htmlspecialchars($username);?>">
                </td>
            </tr>
            <tr>
                <td>
                    Password
                </td>
                <td>
                    <input class="form-control" type="password" id="password">
                </td>
            </tr>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="save-config">Simpan</button>
        <button type="button" class="btn btn-primary" id="test-ping">Coba</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batalkan</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="alerModal" tabindex="-1" role="dialog" aria-labelledby="alerModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="alerModalTitle">Terjadi Kesalahan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

    <div class="button-area">
    <button class="btn btn-success" id="start-sync">Mulai Sinkronisasi</button>
    <button class="btn btn-primary" id="config-sync" data-toggle="modal" data-target="#configModal">Konfigurasi</button>
    </div>
    <div class="sync-message">
        <div class="alert alert-primary"><span class="fas fa-check"></span> Proses sinkronisasi data telah selesai.</div>
    </div>


    <div class="sync-area">
        <div class="sync-container">
            <h4>Sinkronisasi File</h4>
            <div class="sync-item" data-type="file" data-direction="down" data-step="1">
                <div class="sync-label">Download informasi dari sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="file" data-direction="down" data-step="3">
                <div class="sync-label">Download file sync dari sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="file" data-direction="down" data-step="5">
                <div class="sync-label">Download file pengguna dari sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="file" data-direction="up" data-step="1">
                <div class="sync-label">Persiapan file sync untuk diupload</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <div class="sync-item" data-type="file" data-direction="up" data-step="3">
                <div class="sync-label">Upload file pengguna ke sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="file" data-direction="up" data-step="5">
                <div class="sync-label">Upload file sync ke sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="file" data-direction="up" data-step="7">
                <div class="sync-label">Upload informasi ke sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

        </div>
        <div class="sync-container">

            <h4>Sinkronisasi Database</h4>
            <div class="sync-item" data-type="database" data-direction="down" data-step="1">
                <div class="sync-label">Download informasi dari sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="database" data-direction="down" data-step="3">
                <div class="sync-label">Download file sync dari sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="database" data-direction="down" data-step="5">
                <div class="sync-label">Eksekusi query dari file sync</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="database" data-direction="up" data-step="1">
                <div class="sync-label">Persiapan file sync untuk diupload</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="database" data-direction="up" data-step="3">
                <div class="sync-label">Upload file sync ke sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="sync-item" data-type="database" data-direction="up" data-step="5">
                <div class="sync-label">Upload informasi ke sync hub</div>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once dirname(__FILE__) . "/lib.inc/footer.php";
}
?>