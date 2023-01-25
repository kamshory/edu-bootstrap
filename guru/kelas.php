<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/bukan-guru.php";
exit();
}
$cfg->module_title = "Kelas";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_class`.* $nt,
(select `edu_school_program`.`name` from `edu_school_program` where `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) as `school_program_id`
from `edu_class` 
where `edu_class`.`active` = '1' and `edu_class`.`school_id` = '$school_id'
and `edu_class`.`class_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_class" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Kode Kelas</td>
		<td><?php echo $data['class_code'];?></td>
		</tr>
		<tr>
		<td>Tingkat
		</td><td><?php 
echo $picoEdu->getGradeName($data['grade_id']);
?>
<td>
		</tr>
		<tr>
		<td>Jurusan</td>
		<td><?php echo $data['school_program_id'];?></td>
		</tr>
		<tr>
		<td>Nama Kelas
		</td><td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Order</td>
		<td><?php echo $data['order'];?></td>
		</tr>
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
    <span class="search-label">Nama Kelas</span>
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
$sql_filter .= " and (`edu_class`.`name` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';


$sql = "SELECT `edu_class`.* $nt,
(select `edu_school_program`.`name` from `edu_school_program` where `edu_school_program`.`school_program_id` = `edu_class`.`school_program_id` limit 0,1) as `school_program_id`,
(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`class_id` = `edu_class`.`class_id`) as `num_student`
from `edu_class`
where `edu_class`.`school_id` = '$school_id' $sql_filter
order by `edu_class`.`order` asc
";
$sql_test = "SELECT `edu_class`.*
from `edu_class`
where `edu_class`.`active` = '1' and `edu_class`.`school_id` = '$school_id' $sql_filter
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
	.hide-some-cell tr td:nth-child(2), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7){
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(5){
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
      <td>Kode </td>
      <td>Nama</td>
      <td>Tingkat</td>
      <td>Jurusan</td>
      <td>Siswa</td>
      <td>Order</td>
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
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['class_code'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo ($data['grade_id']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['school_program_id'];?></a></td>
      <td><a href="siswa.php?class_id=<?php echo $data['class_id'];?>"><?php echo ($data['num_student']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&class_id=<?php echo $data['class_id'];?>"><?php echo $data['order'];?></a></td>
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