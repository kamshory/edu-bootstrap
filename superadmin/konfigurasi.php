<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(@$cfg->protocol == 'http')
{
	header("Location: https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
}
if(!@$adminLoggedIn->admin_id)
{
	require_once dirname(__FILE__)."/login-form.php";
        exit();
}
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

if(isset($_POST['save']))
{
    foreach($_POST as $key=>$value)
    {
        if(!is_array($value) && $key != 'save')
        {
            $database->setSystemVariable($key, $value);
        }
    }
}

$pageTitle = "Konfigurasi";
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
    <form name="formedu_config" id="formedu_config" action="" method="post" enctype="multipart/form-data">
        <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
            <tr>
                <td>Nama Perangkat</td>
                <td><input type="text" class="form-control input-text" name="device_name" id="device_name" value="<?php echo $database->getSystemVariable('device_name'); ?>" autocomplete="off" /></td>
            </tr>
            <tr>
                <td>URL Dasar</td>
                <td><input type="text" class="form-control input-text" name="base_url" id="base_url" value="<?php echo $database->getSystemVariable('base_url'); ?>" autocomplete="off" /></td>
            </tr>
            <tr>
                <td>URL Siswa</td>
                <td><input type="text" class="form-control input-text" name="base_url_student" id="base_url_student" value="<?php echo $database->getSystemVariable('base_url_student'); ?>" autocomplete="off" /></td>
            </tr>
            <tr>
                <td>URL Ujian</td>
                <td><input type="text" class="form-control input-text" name="base_url_test" id="base_url_test" value="<?php echo $database->getSystemVariable('base_url_test'); ?>" autocomplete="off" /></td>
            </tr>
            <tr>
                <td>URL Sync Hub</td>
                <td><input type="text" class="form-control input-text" name="sync_hub_url" id="sync_hub_url" value="<?php echo $database->getSystemVariable('sync_hub_url'); ?>" autocomplete="off" /></td>
            </tr>
        </table>
        <table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
            <tr>
                <td></td>
                <td>
                    <input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" /> 
                </td>
            </tr>
        </table>
</form>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
