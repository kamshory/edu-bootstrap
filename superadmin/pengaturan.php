<?php
require_once dirname(__DIR__) . "/lib.inc/auth-admin.php";
$pageTitle = "Pengaturan";
if(@$_POST['save'] != '')
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

    $url_ethernet = trim(@$_POST['url_ethernet']);
    if(!empty($url_ethernet))
    {
        $database->setSystemVariable("url_ethernet", addslashes($url_ethernet));
    }

    $url_wifi = trim(@$_POST['url_wifi']);
    if(!empty($url_wifi))
    {
        $database->setSystemVariable("url_wifi", addslashes($url_wifi));
    }

}

if(!empty($school_id)) 
{
    require_once __DIR__ . "/lib.inc/header.php"; //NOSONAR
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

            
        });
        
        function doPing(args, clbkSuccess, clbkFailed)
        {
            args = args || {};
            $.ajax({
                    url: 'lib.tools/sync/?action=ping',
                    data: args,
                    type:'POST',
                    dataType:'json',
                    success:function(response)
                    {
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
        
    </script>

    <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/sync.js"></script>
    
<!-- Modal -->
<?php
    $syncHubURL = $database->getSystemVariable("sync_hub_url");
    $fileSyncUrl = rtrim($syncHubURL, "/")."/";
    $username = $database->getSystemVariable("sync_hub_username");

    $url_ethernet = $database->getSystemVariable("url_ethernet");
    $url_wifi = $database->getSystemVariable("url_wifi");
?>

        <form action="" method="post" enctype="application/x-www-form-urlencoded">
        <table class="table two-side-table responsive-tow-side-table" width="100%">
            <tr>
                <td>
                    URL Perangkat (Ethernet)
                </td>
                <td>
                    <input class="form-control" type="url_ethernet" name="url_ethernet" value="<?php echo htmlspecialchars($url_ethernet);?>">
                </td>
            </tr>
            <tr>
                <td>
                URL Perangkat (Wifi)
                </td>
                <td>
                    <input class="form-control" type="url_wifi" name="url_wifi" value="<?php echo htmlspecialchars($url_wifi);?>">
                </td>
            </tr>
            <tr>
                <td>
                    URL Sync Hub
                </td>
                <td>
                    <input class="form-control" type="url" name="url" value="<?php echo htmlspecialchars($fileSyncUrl);?>">
                </td>
            </tr>
            <tr>
                <td>
                    Username Sync Hub
                </td>
                <td>
                    <input class="form-control" type="text" name="username" value="<?php echo htmlspecialchars($username);?>">
                </td>
            </tr>
            <tr>
                <td>
                    Password Sync Hub
                </td>
                <td>
                    <input class="form-control" type="password" name="password">
                </td>
            </tr>
        </table>
        <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
            <tr><td></td>
            <td><input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> 
            <input type="button" name="showall" id="showall" value="Batalkan" class="btn btn-primary" onclick="window.location='./'" /></td>
            </tr>
        </table>
        </form>
        

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

   

    <?php
    require_once __DIR__ . "/lib.inc/footer.php";
}
?>