<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(@$school_id == 0)
{
include_once dirname(__FILE__)."/login-form.php";
exit();
}
$cfg->module_title = "Siswa";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(@$_GET['option']=='detail')
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_UINT);
$nt = '';
$sql = "SELECT `edu_student`.* , `edu_school_program`.`name` as `school_program_name`,
`edu_class`.`name` as `class_name`
from `edu_student` 
left join (`edu_class`) on(`edu_class`.`class_id` = `edu_student`.`class_id`)
left join (`edu_school_program`) on(`edu_school_program`.`school_program_id` = `edu_class`.`school_program_id`)
where 1
and `edu_student`.`student_id` = '$edit_key' and `edu_student`.`school_id` = '$school_id'
group by `edu_student`.`student_id`
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_student" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Jenis Kelamin</td>
		<td><?php echo $picoEdu->getGenderName($data['gender']);?></td>
		</tr>
    <?php
	if($data['reg_number'] != '')
	{
	?>
		<tr>
		<td>NIS</td>
		<td><?php echo ($data['reg_number']);?></td>
		</tr>
	<?php
	}
	if($data['reg_number_national'] != '')
	{
	?>
    	<tr>
		<td>NISN</td>
		<td><?php echo ($data['reg_number_national']);?></td>
		</tr>
	<?php
	}
	if($data['grade_id'] != '' && $data['grade_id'] != 0)
	{
	?>
		<tr>
		<td>Tingkat</td>
		<td><?php 
echo $picoEdu->getGradeName($data['grade_id']);
?>
<td>
		</tr>
	<?php
	}
	if($data['class_name'] != '')
	{
	?>
		<tr>
		<td>Kelas</td>
		<td><?php echo $data['class_name'];?></td>
		</tr>
	<?php
	}
	if($data['school_program_name'] != '')
	{
	?>
		<tr>
		<td>Jurusan</td>
		<td><?php echo ($data['school_program_name']);?></td>
		</tr>
	<?php
	}
	?>
		<tr>
		<td></td>
		<td><input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo 'siswa.php';?>'" /></td>
		</tr>
	</table>
</form>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo 'siswa.php';?>">Klik di sini untuk kembali.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_NUMBER_UINT);
?>
<script type="text/javascript">
$(document).ready(function(e) {
    $(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
});
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Kelas</span>
  <select class="input-select" name="class_id" id="class_id">
    <option value=""></option>
    <?php 
    $sql2 = "select * from `edu_class` where `active` = '1' and `school_id` = '$school_id' order by `order` asc ";
    echo $picoEdu->createFilterDb(
		$sql2,
		array(
			'attributeList'=>array(
				array('attribute'=>'value', 'source'=>'class_id')
			),
			'selectCondition'=>array(
				'source'=>'class_id',
				'value'=>$class_id
			),
			'caption'=>array(
				'delimiter'=>' &raquo; ',
				'values'=>array(
					'name'
				)
			)
		)
	);

    ?>
    </select>
    <span class="search-label">Nama Siswa</span>
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
$sql_filter .= " and (`edu_student`.`name` like '%".addslashes($pagination->query)."%' )";
}
if($class_id != 0)
{
	$pagination->array_get[] = 'class_id';
	$sql_filter .= " and (`edu_student`.`class_id` = '$class_id' )";
}

$nt = '';


$sql = "SELECT `edu_student`.* , `edu_class`.`name` as `class_id`, `edu_class`.`order` as `order`
from `edu_student`
left join(`edu_class`) on(`edu_class`.`class_id` = `edu_student`.`class_id`)
where `edu_student`.`school_id` = '$school_id' $sql_filter
order by `order` asc, `edu_student`.`name` asc
";

$sql_test = "SELECT `edu_student`.`student_id`
from `edu_student`
left join(`edu_class`) on(`edu_class`.`class_id` = `edu_student`.`class_id`)
where `edu_student`.`school_id` = '$school_id' $sql_filter
";

$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
{
if($class_id == 0)
{
$pagination->start = $pagination->offset+1;
$pagination->end = $pagination->offset+$pagination->total_record_with_limit;

$pagination->result = $picoEdu->createPagination('siswa.php', $pagination->total_record, $pagination->limit, $pagination->num_page, 
$pagination->offset, $pagination->array_get, true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next); 
$pagination->str_result = "";
foreach($pagination->result as $i=>$obj)
{
$cls = ($obj->sel)?" class=\"pagination-selected\"":"";
$pagination->str_result .= "<a href=\"".$obj->ref."\"$cls>".$obj->text."</a> ";
}
}
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(2), .hide-some-cell tr td:nth-child(4){
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(6){
		display:none;
	}
}
</style>
<?php
if($class_id == 0)
{
?>
<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>
<?php
}
?>
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
  <thead>
    <tr>
      <td width="25">No</td>
      <td>NIS</td>
      <td>Nama</td>
      <td>Tingkat</td>
      <td>Kelas</td>
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
      <td><a href="<?php echo 'siswa.php';?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo ($data['reg_number']);?></a></td>
      <td><a href="<?php echo 'siswa.php';?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo 'siswa.php';?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo ($data['grade_id']);?></a></td>
      <td><a href="<?php echo 'siswa.php';?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo ($data['class_id']);?></a></td>
      <td><a href="<?php echo 'siswa.php';?>?option=detail&student_id=<?php echo $data['student_id'];?>"><?php echo ($data['gender']);?></a></td>
      </tr>
    <?php
	}
	?>
    </tbody>
  </table>
<?php
if($class_id == 0)
{
?>
<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>
<?php
}
?>
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