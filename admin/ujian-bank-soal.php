<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/bukan-admin.php";
	exit();
}
if(empty(@$real_school_id))
{
	include_once dirname(__FILE__)."/belum-ada-sekolah.php";
	exit();
}
$cfg->module_title = "Bank Soal";
include_once dirname(__FILE__)."/lib.inc/header.php";
$grade_id = kh_filter_input(INPUT_GET, 'grade_id', FILTER_SANITIZE_NUMBER_UINT);
$array_class = $picoEdu->getArrayClass($school_id);
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test-import.css">
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/test-import.js"></script>
<div class="collection-list">
	<div class="collection-selector">
    	<div class="grade-selector">
        	<form name="gradefrm" id="gradefrm" method="get" enctype="multipart/form-data">
            	<select name="grade_id" id="grade_id">
                	<option value="">- Pilih Tingkat -</option>
                	<option value="1"<?php if($grade_id=='1') echo ' selected="selected"';?>>Kelas 1 SD</option>
                	<option value="2"<?php if($grade_id=='2') echo ' selected="selected"';?>>Kelas 2 SD</option>
                	<option value="3"<?php if($grade_id=='3') echo ' selected="selected"';?>>Kelas 3 SD</option>
                	<option value="4"<?php if($grade_id=='4') echo ' selected="selected"';?>>Kelas 4 SD</option>
                	<option value="5"<?php if($grade_id=='5') echo ' selected="selected"';?>>Kelas 5 SD</option>
                	<option value="6"<?php if($grade_id=='6') echo ' selected="selected"';?>>Kelas 6 SD</option>
                	<option value="7"<?php if($grade_id=='7') echo ' selected="selected"';?>>Kelas 7 SMP</option>
                	<option value="8"<?php if($grade_id=='8') echo ' selected="selected"';?>>Kelas 8 SMP</option>
                	<option value="9"<?php if($grade_id=='9') echo ' selected="selected"';?>>Kelas 9 SMP</option>
                	<option value="10"<?php if($grade_id=='10') echo ' selected="selected"';?>>Kelas 10 SMA</option>
                	<option value="11"<?php if($grade_id=='11') echo ' selected="selected"';?>>Kelas 11 SMA</option>
                	<option value="12"<?php if($grade_id=='12') echo ' selected="selected"';?>>Kelas 12 SMA</option>
                	<option value="13"<?php if($grade_id=='13') echo ' selected="selected"';?>>Perguruan Tinggi</option>
                </select>
            </form>
        </div>
        <div class="file-list">
        	<ul>
            	<?php
				if($grade_id != 0)
				{
					$filter = " and `grade_id` = '$grade_id' ";
				}
				else
				{
					$filter = "";
				}
				$sql = "SELECT `edu_test_collection`.* from `edu_test_collection` 
				where `edu_test_collection`.`active` = '1' $filter
				order by `test_collection_id` desc
				";
				$stmt = $database->executeQuery($sql);
				
				if($stmt->rowCount() > 0)
				{
					$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
					foreach($rows as $data)
					{
					?>
					<li><a href="#" data-id="<?php echo $data['test_collection_id'];?>"><?php echo $data['name'];?></a></li>
					<?php
					}
				}
				?>
            </ul>
        </div>
    </div>
    <div class="collection-preview-container" data-id="0">
    	<div class="import-tools">
        	<a href="#" class="create-new">Untuk Ujian Baru</a>
        	<a href="#" class="select-existing">Untuk Ujian Yang Ada</a>
        </div>
        <div class="test-selector-container">
        <div class="test-selector-wrapper">
            <div class="close-test-selector"><a href="#">&#10006;</a></div>
            <h3>Pilih Ujian</h3>
        	<div class="test-selector-inner">
        	<?php

			$sql = "SELECT `edu_test`.*,
			(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher`,
			(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id`) as `question`
			from `edu_test`
			where 1 and `edu_test`.`school_id` = '$school_id' 
			order by `edu_test`.`test_id` desc
			";
			$stmt = $database->executeQuery($sql);
				
				if($stmt->rowCount() > 0)
				{
					
			?>
              <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table">
              <thead>
                <tr>
                  <td>Ujian</td>
                  <td>Kelas</td>
                  <td>Pelajaran</td>
                  <td>Soal</td>
                 </tr>
                </thead>
                <tbody>
                <?php
                $no=0;
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($rows as $data)
				{
                $no++;
                ?>
                <tr>
                  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian-daftar.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
                  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian-daftar.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
                  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian-daftar.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
                  <td><a target="_blank" href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['question']);?></a></td>
                 </tr>
                <?php
                }
                ?>
                </tbody>
              </table>
			  <?php
			  }
			  ?>
              </div>
        </div>
        </div>
    	<div class="file-preview">
            

        </div>
    </div>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
?>
