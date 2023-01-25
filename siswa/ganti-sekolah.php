<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(@$school_id == 0)
{
include_once dirname(__FILE__)."/login-form.php";
exit();
}
$cfg->module_title = "Pilih Sekolah";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(@$_GET['option']=='select')
{
	$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_INT);
	$sql = "
	select `edu_school3`.* from(
	
	select `edu_school1`.`school_id`, `edu_school1`.`name`, `edu_school1`.`school_grade_id`, `edu_school1`.`public_private`, 
	`edu_school1`.`principal`, `edu_school1`.`active`, `edu_school1`.`open`, `edu_member_school`.`role`
	from `edu_member_school`
	inner join(`edu_school` as `edu_school1`) on(`edu_school1`.`school_id` = `edu_member_school`.`school_id`)
	where `edu_member_school`.`member_id` = '$student_id' and `edu_member_school`.`role` = 'S'
	) as `edu_school3`
	where 1 and `edu_school3`.`school_id` = '$school_id'
	having `edu_school3`.`role` = 'S'
	order by `edu_school3`.`open` asc, `edu_school3`.`name` asc
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$sql = "update `edu_student` set `school_id` = '$school_id' where `student_id` = '$student_id' ";
		$database->executeUpdate($sql);
		header('Location: index.php');
		exit();
	}

}
$base_dir = 'siswa/';
$school_code_from_parser = 'student';
if(@$_GET['option']=='detail')
{
include_once dirname((__FILE__))."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_NUMBER_INT);
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`school_id` = `edu_school`.`school_id`) as `student`,
(select `country`.`name` from `country` where `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_school`.`admin_create`) as `admin_create`,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_school`.`admin_edit`) as `admin_edit`
from `edu_school` 
where 1
and `edu_school`.`school_id` = '$edit_key'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
	<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
		<tr>
		<td>Nama Sekolah</td>
		<td><?php echo $data['name'];?></td>
		</tr>
		<tr>
		<td>Jenjang</td>
		<td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?></td>
		</tr>
		<tr>
		<td>Negeri/Swasta</td>
		<td><?php if($data['public_private']=='U') echo 'Negeri'; if($data['public_private']=='I') echo 'Swasta';?></td>
		</tr>
		<tr>
		<td>Kepala Sekolah</td>
		<td><?php echo $data['principal'];?></td>
		</tr>
		<tr>
		<td>Alamat</td>
		<td><?php echo $data['address'];?></td>
		</tr>
		<tr>
		<td>Telepon</td>
		<td><?php echo $data['phone'];?></td>
		</tr>
		<tr>
		<td>Email</td>
		<td><?php echo $data['email'];?></td>
		</tr>
		<tr>
		<td>Bahasa</td>
		<td><?php if($data['language']=='en') echo 'English'; if($data['language']=='id') echo 'Bahasa Indonesia';?></td>
		</tr>
		<tr>
		<td>Negara</td>
		<td><?php echo $data['country_id'];?></td>
		</tr>
		<tr>
		<td>Provinsi</td>
		<td><?php echo $data['state_id'];?></td>
		</tr>
		<tr>
		<td>Kabupaten/Kota</td>
		<td><?php echo $data['city_id'];?></td>
		</tr>
		<tr>
		<td>Jumlah Siswa</td>
		<td><?php echo ($data['student']);?> siswa</td>
		</tr>
		<tr>
		<td>Dibuat</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></td>
		</tr>
		<tr>
		<td>Diubah</td>
		<td><?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_edit'])));?></td>
		</tr>
		<tr>
		<td>Admin Buat</td>
		<td><?php echo $data['admin_create'];?></td>
		</tr>
		<tr>
		<td>Admin Ubah</td>
		<td><?php echo $data['admin_edit'];?></td>
		</tr>
		<tr>
		<td>Aktif</td>
		<td><?php echo ($data['active'])?'Ya':'Tidak';?></td>
		</tr>
		<tr>
		<td></td>
		<td>
        <input type="button" name="select" id="select" class="com-button" value="Pilih" onClick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=select&school_id=<?php echo $data['school_id'];?>'" /> 
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onClick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" />
        </td>
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
include_once dirname((__FILE__))."/lib.inc/footer.php";

}
else
{
include_once dirname((__FILE__))."/lib.inc/header.php";
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Sekolah</span>
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
$sql_filter .= " and (`edu_school3`.`name` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';

$sql = "
select `edu_school3`.* from(

select `edu_school1`.`school_id`, `edu_school1`.`name`, `edu_school1`.`school_grade_id`, `edu_school1`.`public_private`, 
`edu_school1`.`principal`, `edu_school1`.`active`, `edu_school1`.`open`, `edu_member_school`.`role`
from `edu_member_school`
inner join(`edu_school` as `edu_school1`) on(`edu_school1`.`school_id` = `edu_member_school`.`school_id`)
where `edu_member_school`.`member_id` = '$student_id' and `edu_member_school`.`role` = 'S'
) as `edu_school3`
where 1 $sql_filter
having `edu_school3`.`role` = 'S' and `edu_school3`.`open` = '1'
order by `edu_school3`.`name` asc
";
$sql_test = $sql;
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
@media screen and (max-width:599px)
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
      <td>Nama Sekolah</td>
      <td>Jenjang</td>
      <td>N/S</td>
      <td>Kepala Sekolah</td>
      </tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->offset;
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	$cls = "";
	if($data['active']) $cls .= "data-active";
	else $cls .= "data-inactive";
	if($data['school_id'] == @$auth_school_id) $cls .= " data-default";
	?>
    <tr class="<?php echo $cls;?>">
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php if($data['public_private']=='U') echo 'Negeri'; if($data['public_private']=='I') echo 'Swasta';?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&school_id=<?php echo $data['school_id'];?>"><?php echo $data['principal'];?></a></td>
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
<div class="warning">Anda tidak bisa mengganti sekolah.</div>
<?php
}
?>
</div>

<?php
include_once dirname((__FILE__))."/lib.inc/footer.php";
}
?>