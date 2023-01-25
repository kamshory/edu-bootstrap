<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-guru.php";
exit();
}
$cfg->module_title = "Guru";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'teacher_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_teacher`.* $nt
from `edu_teacher` 
where `edu_teacher`.`active` = '1' and `edu_teacher`.`school_id` = '$school_id'
and `edu_teacher`.`teacher_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_teacher" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><?php echo $picoEdu->getGenderName($data['gender']);?></td>
		</tr>
		<tr>
		<td>No.Induk</td>
		<td><?php echo ($data['reg_number']);?></td>
		</tr>
		<tr>
		<td>NUPTK</td>
		<td><?php echo ($data['reg_number_national']);?></td>
		</tr>
	<?php
	if($data['birth_place'] != '')
	{
	?>
		<tr>
		<td>Tempat Lahir</td>
		<td><?php echo $data['birth_place'];?></td>
		</tr>
	<?php
	}
	if($data['birth_place'] != '0000-00-00')
	{
	?>
		<tr>
		<td>Tanggal Lahir</td>
		<td><?php echo translateDate(date('d F Y', strtotime($data['birth_day'])));?></td>
		</tr>
	<?php
	}
	if($data['address'] != '')
	{
	?>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?></td>
		</tr>
	<?php
	}
	if($data['phone'] != '')
	{
	?>
		<tr>
		<td>Telepon</td>
		<td><?php echo $data['phone'];?></td>
		</tr>
	<?php
	}
	if($data['email'] != '')
	{
	?>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
	<?php
	}
	?>
		<tr>
		<td></td>
		<td><input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Nama Guru</span>
    <input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
 "))));?>" />
  <input type="submit" name="search" id="search" value="Cari" class="com-button" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";
$pagination->array_get = array();
if($pagination->query){
$pagination->array_get[] = 'q';
$sql_filter .= " and (`edu_teacher`.`name` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';

$sql = "SELECT `edu_teacher`.* $nt
from `edu_teacher`
where `edu_teacher`.`active` = '1' and `edu_teacher`.`school_id` = '$school_id' $sql_filter
order by `edu_teacher`.`teacher_id` asc
";
$sql_test = "SELECT `edu_teacher`.*
from `edu_teacher`
where `edu_teacher`.`active` = '1' and `edu_teacher`.`school_id` = '$school_id' $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
{
$pagination->start = $pagination->offset+1;
$pagination->end = $pagination->offset+$pagination->total_record_with_limit;

$pagination->result = $picoEdu->createPagination(basename($_SERVER['PHP_SELF']), $pagination->total_record, $pagination->limit, $pagination->num_page, 
$pagination->offset, $pagination->array_get, true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next); 
$pagination->str_result = "";
foreach($pagination->result as $i=>$obj)
{
$cls = ($obj->sel)?" class=\"pagination-selected\"":"";
$pagination->str_result .= "<a href=\"".$obj->ref."\"$cls>".$obj->text."</a> ";
}
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(2), .hide-some-cell tr td:nth-child(3), .hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(8){
		display:none;
	}
}
</style>

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
  <thead>
    <tr>
      <td width="25">No</td>
      <td>No.Induk</td>
      <td>NUPTK</td>
      <td>Nama</td>
      <td>L/P</td>
      </tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr<?php echo (@$data['active'])?" class=\"data-active\"":" class=\"data-inactive\"";?>>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo ($data['reg_number']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo ($data['reg_number_national']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&teacher_id=<?php echo $data['teacher_id'];?>"><?php if($data['gender']=='M') echo 'L'; if($data['gender']=='W') echo 'P';?></a></td>
      </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>

</form>
<?php
}
else if(@$_GET['q'])
{
?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>