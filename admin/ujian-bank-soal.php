<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/bukan-admin.php";
	exit();
}
if(empty($real_school_id))
{
	require_once dirname(__FILE__)."/belum-ada-sekolah.php";
	exit();
}
$pageTitle = "Bank Soal";
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$grade_id = kh_filter_input(INPUT_GET, "grade_id", FILTER_SANITIZE_STRING_NEW);
$array_class = $picoEdu->getArrayClass($school_id);
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test-import.css">
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/test-import.js"></script>
<div class="collection-list">
	<div class="collection-selector">
    	<div class="grade-selector">
        	<form name="gradefrm" id="gradefrm" method="get" enctype="multipart/form-data">
            	<select class="form-control" name="grade_id" id="grade_id">
                	<option value="">- Pilih Tingkat -</option>
                	<?php
					echo $picoEdu->createGradeOption($grade_id);
					?>
                </select>
            </form>
        </div>
        <div class="file-list">
        	<ul>
            	<?php
				if($grade_id != 0)
				{
					$filter = " AND `grade_id` = '$grade_id' ";
				}
				else
				{
					$filter = "";
				}
				$sql = "SELECT `edu_test_collection`.* FROM `edu_test_collection` 
				WHERE `edu_test_collection`.`active` = true $filter
				ORDER BY `test_collection_id` DESC
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
			(SELECT `edu_teacher`.`name` FROM `edu_teacher` WHERE `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) AS `teacher`,
			(SELECT COUNT(DISTINCT `edu_question`.`question_id`) FROM `edu_question` WHERE `edu_question`.`test_id` = `edu_test`.`test_id`) AS `question`
			FROM `edu_test`
			WHERE `edu_test`.`school_id` = '$school_id' 
			ORDER BY `edu_test`.`test_id` DESC
			";
			$stmt = $database->executeQuery($sql);
				
				if($stmt->rowCount() > 0)
				{
					
			?>
              <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm">
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
                  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
                  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" title="<?php echo htmlspecialchars($class);?>" data-toggle="tooltip" data-html="true" data-class="<?php echo htmlspecialchars($data['class']);?>"><?php echo $class_sort;?></a></td>
                  <td><a class="import-question" data-test-id="<?php echo $data['test_id'];?>" href="ujian.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
                  <td><a target="_blank" href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['question'];?></a></td>
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
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
?>
