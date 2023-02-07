<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";

include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$sql = "SELECT `edu_school`.*,
(SELECT `country`.`name` FROM `country` WHERE `country`.`country_id` = `edu_school`.`country_id`) AS `country_id`,
(SELECT `state`.`name` FROM `state` WHERE `state`.`state_id` = `edu_school`.`state_id`) AS `state_id`,
(SELECT `city`.`name` FROM `city` WHERE `city`.`city_id` = `edu_school`.`city_id`) AS `city_id`,
(select count(distinct `edu_class`.`class_id`) FROM `edu_class` WHERE `edu_class`.`school_id` = `edu_school`.`school_id` group by `edu_class`.`school_id` limit 0,1) AS `num_class`,
(select count(distinct `edu_teacher`.`teacher_id`) FROM `edu_teacher` WHERE `edu_teacher`.`school_id` = `edu_school`.`school_id` group by `edu_teacher`.`school_id` limit 0,1) AS `num_teacher`,
(select count(distinct `edu_student`.`student_id`) FROM `edu_student` WHERE `edu_student`.`school_id` = `edu_school`.`school_id` group by `edu_student`.`school_id` limit 0,1) AS `num_student`
FROM `edu_school` 
WHERE `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$school_name = $data['name'];
$school_code = $data['school_code'];
$cfg->page_title = "Tentang ".$school_name;
?>
<table width="100%" border="0" class="table two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
    <tr>
    <td>Nama Sekolah</td>
    <td><?php echo $data['name'];?> </td>
    </tr>
    <tr>
    <td>Jenjang Sekolah</td>
    <td><?php echo $picoEdu->getSchoolGradeName($data['school_grade_id']);?> </td>
    </tr>
    <tr>
    <td>Negeri/Swasta</td>
    <td><?php echo $picoEdu->selectFromMap($data['public_private'], array('U'=>'Negeri', 'I'=>'Swasta'));?> </td>
    </tr>
    <tr>
    <td>Kepala Sekolah</td>
    <td><?php echo $data['principal'];?> </td>
    </tr>
    <tr>
    <td>Alamat Sekolah</td>
    <td><?php echo $data['address'];?> </td>
    </tr>
    <tr>
    <td>Telepon Sekolah</td>
    <td><?php echo $data['phone'];?> </td>
    </tr>
    <tr>
    <td>Email Sekolah</td>
    <td><?php echo $data['email'];?> </td>
    </tr>
    <tr>
    <td>Bahasa</td>
    <td><?php echo $picoEdu->selectFromMap($data['language'], array('en'=>'English', 'id'=>'Bahasa Indonesia'));?> </td>
    </tr>
    <tr>
    <td>Negara</td>
    <td><?php echo $data['country_id'];?> </td>
    </tr>
    <tr>
    <td>Provinsi</td>
    <td><?php echo $data['state_id'];?> </td>
    </tr>
    <tr>
    <td>Kabupaten/Kota</td>
    <td><?php echo $data['city_id'];?> </td>
    </tr>
    <tr>
      <td>Jumlah Kelas</td>
      <td><?php echo $data['num_class'];?> </td>
    </tr>
    <tr>
      <td>Jumlah Siswa</td>
      <td><?php echo $data['num_student'];?> orang</td>
    </tr>
    <tr>
      <td>Jumlah Guru</td>
      <td><?php echo $data['num_teacher'];?> orang</td>
    </tr>
</table>
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>