<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";

include_once dirname(__FILE__)."/lib.inc/header.php";
$sql = "SELECT `edu_school`.*,
(select `country`.`name` from `country` where `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`,
(select count(distinct `edu_class`.`class_id`) from `edu_class` where `edu_class`.`school_id` = `edu_school`.`school_id` group by `edu_class`.`school_id` limit 0,1) as `num_class`,
(select count(distinct `edu_teacher`.`teacher_id`) from `edu_teacher` where `edu_teacher`.`school_id` = `edu_school`.`school_id` group by `edu_teacher`.`school_id` limit 0,1) as `num_teacher`,
(select count(distinct `edu_student`.`student_id`) from `edu_student` where `edu_student`.`school_id` = `edu_school`.`school_id` group by `edu_student`.`school_id` limit 0,1) as `num_student`
from `edu_school` 
where 1
and `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$school_name = $data['name'];
$school_code = $data['school_code'];
$cfg->page_title = "Tentang ".$school_name;
?>
<table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
    <tr>
    <td>Nama Sekolah</td>
    <td><?php echo $data['name'];?></td>
    </tr>
    <tr>
    <td>Jenjang Sekolah</td>
    <td><?php if($data['school_grade_id'] == 3) echo 'SD Sederajat'; if($data['school_grade_id'] == 4) echo 'SMP Sederajat'; if($data['school_grade_id'] == 5) echo 'SMA Sederajat';?></td>
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
    <td>Alamat Sekolah</td>
    <td><?php echo $data['address'];?></td>
    </tr>
    <tr>
    <td>Telepon Sekolah</td>
    <td><?php echo $data['phone'];?></td>
    </tr>
    <tr>
    <td>Email Sekolah</td>
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
      <td>Jumlah Kelas</td>
      <td><?php echo ($data['num_class']);?></td>
    </tr>
    <tr>
      <td>Jumlah Siswa</td>
      <td><?php echo ($data['num_student']);?> orang</td>
    </tr>
    <tr>
      <td>Jumlah Guru</td>
      <td><?php echo ($data['num_teacher']);?> orang</td>
    </tr>
</table>
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>