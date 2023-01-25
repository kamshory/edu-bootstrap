<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
$cfg->module_title = "Version Program";
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<style type="text/css">
.table-version
{
	min-width:500px;
	border-collapse:collapse;
}
.table-version td{
	padding:4px 5px;
}
.table-version td p{
	margin:2px 0;
}
.table-version thead{
	font-weight:bold;
}
.table-version tbody td{
	vertical-align:top;
}
.table-version tbody td h3, .table-version tbody td h4, .table-version tbody td h5, .table-version tbody td ul, .table-version tbody td ol{
	margin:5px 0px;
}
.table-version tbody td p, .table-version tbody td li{
	line-height:1.5;
}
.table-version tbody td.cell-info{
	padding:0;
	overflow:hidden;;
}
.table-version tbody td.cell-info table{
	margin:-1px -1px;
	border-collapse:collapse;
	z-index:-1;
}
@media screen and (max-width:599px)
{
	.table-version{
		border:none;
		width:100%;
		min-width:100%;
		box-sizing:border-box;
	}
	.table-version table{
		border:none;
		width:100%;
		box-sizing:border-box;
	}
	.table-version td{
		border:none;
		position:relative;
		width:100%;
		box-sizing:border-box;
	}
	.table-version > tbody > tr > td:nth-child(2){
		border-bottom:1px solid #DDDDDD;
		padding-top:10px;
		padding-bottom:10px;
		margin-bottom:10px;
	}
	.table-version > tbody .cell-info tr td:nth-child(1){
		font-weight:bold;
	}
	.table-version > tbody .cell-info tr td:nth-child(2){
		font-weight:normal;
	}
	.table-version table td{
		border:none;
		position:relative;
		width:100%;
		display:block;
		box-sizing:border-box;
	}
	.table-version thead{
		display:none;
	}
	.table-version tbody > tr > td{
		display:block;
	}
}
</style>
<table width="100%" border="1" cellspacing="0" cellpadding="0" class="table-version">
<thead>
  <tr>
    <td width="178">Informasi Versi</td>
    <td>Catatan Perubahan</td>
  </tr>
</thead>
<tbody>
<?php
$sql = "SELECT `version`.*
from `version`
where `change_log` != '' and `change_log` is not null
order by `time_release` desc
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
  <tr>
    <td class="cell-info"><table width="190" border="1" cellspacing="0" cellpadding="0">
      <tr>
        <td width="45">Versi</td>
        <td><?php echo $data['version_id'];?></td>
      </tr>
      <tr>
        <td>Rilis</td>
        <td><?php echo translateDate(date('j M Y H:i', strtotime($data['time_release'])));?></td>
      </tr>
      <tr>
        <td>Update</td>
        <td><?php echo translateDate(date('j M Y H:i', strtotime($data['time_update'])));?></td>
      </tr>
    </table></td>
    <td class="cell-log">
    <?php echo $data['change_log'];?>
    </td>
  </tr>
<?php
}
?>
  </tbody>
</table>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>