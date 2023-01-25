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
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";

$cfg->module_title = "Impor Soal Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(isset($_POST['import']) && isset($_POST['test_id']) && isset($_FILES['file']))
{
	// sesuai dengan login masing-masning
	$time_create = $time_edit = $picoEdu->getLocalDateTime();	
	$member_create = $member_edit = $admin_id;
	//
	
	$test_id = kh_filter_input(INPUT_POST, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$picoEdu->sortQuestion($test_id);
	
	$sql = "SELECT `edu_test`.*, 
	(select `edu_question`.`order` from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` order by `order` desc limit 0,1) as `order`
	from `edu_test`
	where `edu_test`.`test_id` = '$test_id'
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);

		$random = $data['random'];
		$order = $data['order'];
		$score_standar = $data['standard_score'];

		$test_dir = dirname(dirname(__FILE__))."/media.edu/school";
		if(!file_exists($test_dir))
		{
			mkdir($test_dir);
		}
		$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id";
		if(!file_exists($test_dir))
		{
			mkdir($test_dir);
		}
		$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test";
		if(!file_exists($test_dir))
		{
			mkdir($test_dir);
		}
		$test_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
		if(!file_exists($test_dir))
		{
			mkdir($test_dir);
		}
		$base_src = "media.edu/school/$school_id/test/$test_id";
		
		$temp_dir = $test_dir;
		
		$basename = md5($_SERVER['REMOTE_ADDR'].'.'.time().'.'.mt_rand(111111, 999999));		
		$path = $temp_dir."/$basename.xml";
		
		$success = move_uploaded_file($_FILES['file']['tmp_name'], $path);
		$xml_data = '';
		$is_zip = false;

		if($success)
		{
			$zip = new ZipArchive();
			$res = $zip->open($path, ZipArchive::CHECKCONS);
			$err = false;
			if ($res !== TRUE)
			{
				switch($res)
				{
					case ZipArchive::ER_NOZIP:
					$err = true;
					break;
					case ZipArchive::ER_INCONS :
					$err = true;
					break;
					case ZipArchive::ER_CRC :
					default:
					$err = true;
					break;
					$err = true;
					break;
				}
			}			
			if($err)
			{
				// if file is not zip file
				$is_zip = false;
				$xml_data = file_get_contents($path);
			}
			else
			{
				// if file is zip file
				$is_zip = true;
				$temp_dir = $test_dir."/".session_id();
				if(!file_exists($temp_dir))
				{
					mkdir($temp_dir);
				}
				$xml_data = '';
				$zip->extractTo($temp_dir.'/');
    			$zip->close();
				$file_list = array();
				
				if($dh = opendir($temp_dir))
				{
					while (($file = readdir($dh)) !== false){
						$file_list[] = $temp_dir.'/'.$file;
						$arr = explode(".", $temp_dir.'/'.$file);
						if(strtolower(end($arr)) == 'xml')
						{
							$xml_data = file_get_contents($temp_dir.'/'.$file);
							break;
						}
						else if(strtolower(end($arr)) == 'txt')
						{
							$xml_data = "\r\n".file_get_contents($temp_dir.'/'.$file);
						}
					}
					closedir($dh);
				}
			}
			unlink($path);

			if(stripos($xml_data, '<?xml') === false)
			{
				// Format Plain				
				$clear_data = parseRawQuestion($xml_data);
				$database->executeQuery('start transaction');
				$oke = 1;
				foreach($clear_data as $question_no=>$question)
				{
					$object = parseQuestion($question);

					if(isset($object['question']) && isset($object['numbering']) && isset($object['option']))
					{
						$content = addslashes(nl2br(UTF8ToEntities(filter_html(addImages(@$object['question'], $test_dir, $base_src, $temp_dir)))));
						$numbering = addslashes($object['numbering']);
						$digest = md5($object['question']);
						$order++;

						$question_id = $database->generateNewId();
						
						$sql1 = "INSERT INTO `edu_question` 
						(`question_id`, `content`, `test_id`, `order`, `multiple_choice`, `random`, `numbering`, `digest`, 
						`time_create`, `member_create`, `time_edit`, `member_edit`, `active`) VALUES
						('$question_id', '$content', '$test_id', '$order', '1', '$random', '$numbering', '$digest', 
						'$time_create', '$member_create', '$time_edit', '$member_edit', '1');
						";
				
						$stmt1 = $database->executeQuery($sql1);
						if($stmt1->rowCount() == 0)
						{
							$oke = $oke * 0;
						}
						else
						{
							if(@is_array($object['option']) && count($object['option']))
							{
								foreach($object['option'] as $option_no=>$option)
								{
									$isi_option = addslashes(nl2br(UTF8ToEntities(filter_html(addImages($option['text'], $test_dir, $base_src, $temp_dir)))));
									$order_option = $option_no+1;
									$score_option = addslashes(@$option['value']*$score_standar); if($score_option == 0) $score_option = addslashes(@$option['score']*$score_standar);

									$option_id = $database->generateNewId();
									$sql2 = "INSERT INTO `edu_option` 
									(`option_id`, `question_id`, `content`, `order`, `score`, 
									`time_create`, `member_create`, `time_edit`, `member_edit`, `active`) VALUES
									('$option_id', '$question_id', '$isi_option', '$order_option', '$score_option', 
									'$time_create', '$member_create', '$time_edit', '$member_edit', '1');
									";
									$stmt2 = $database->executeInsert($sql2);
									if($stmt2->rowCount() == 0)
									{
										$oke = $oke * 0;
									}
								
								}
							}
						}
					}
				}
				if($oke)
				{
					$database->executeQuery('commit');
				}
				else
				{
					$database->executeQuery('rollback');
				}
			}
			else
			{
				// Format XML
				$test_data = simplexml_load_string($xml_data);
				$number_of_question = count(@$test_data->item);
				if($number_of_question)
				{
					foreach($test_data->item as $index_question => $question)
					{
						// petanyaan
						$text_pertanyaan = trim(@$question->question->text);
						$random = trim(@$question->question->random)*1;
						$numbering = addslashes(trim(@$question->question->numbering));
						$competence = addslashes(trim(@$question->question->competence));
						$order++;
						$array_search = array();
						$array_replace = array();
						if(count(@$question->question->file))
						{
							foreach($question->question->file as $index_file_question => $file)
							{
								$name_file = trim(@$file->name, " \r\n\t ");
								$type_file = trim(@$file->type, " \r\n\t ");
								$encoding_file = trim(@$file->encoding, " \r\n\t ");
								$data_file = trim(@$file->data, " \r\n\t ");
								if(stripos($encoding_file, "base64") !== false)
								{
									$data_file = base64_decode($data_file);
								}
								$name_file_repaired = str_replace(".svg+xml", ".svg", $name_file);
								$array_search[] = $name_file;
								$array_replace[] = $name_file_repaired;
								file_put_contents($test_dir."/".$name_file_repaired, $data_file);
							}
						}
						$pertanyaan = htmlspecialchars_decode(replaceBase($text_pertanyaan, $base_src."/"));
						$pertanyaan = str_replace($array_search, $array_replace, $pertanyaan);
						$digest = md5($pertanyaan);
						$pertanyaan = addslashes($pertanyaan);

						$question_id = $database->generateNewId();
						
						$sql1 = "INSERT INTO `edu_question` 
						(`question_id`, `content`, `test_id`, `multiple_choice`, `order`, `random`, `numbering`, `digest`, `basic_competence`,
						`time_create`, `member_create`, `time_edit`, `member_edit`) values
						('$question_id', '$pertanyaan', '$test_id', '1', '$order', '$random', '$numbering', '$digest', '$competence',
						'$time_create', '$member_create', '$time_edit', '$member_edit'); 
						";
						$stmt1 = $database->executeQuery($sql1);
						if ($stmt1->rowCount() > 0 && count(@$question->answer->option) > 0) {

							foreach ($question->answer->option as $index_option => $option) {
								$text_option = trim(@$option->text);
								$score = trim(@$option->value) * 1;
								$array_search = array();
								$array_replace = array();
								if (count(@$option->file)) {
									foreach ($option->file as $index_file_question => $file) {
										$name_file = trim(@$file->name, " \r\n\t ");
										$type_file = trim(@$file->type, " \r\n\t ");
										$encoding_file = trim(@$file->encoding, " \r\n\t ");
										$data_file = trim(@$file->data, " \r\n\t ");

										if (stripos($encoding_file, "base64") !== false) {
											$data_file = base64_decode($data_file);
										}
										file_put_contents($test_dir . "/" . $name_file, $data_file);
										$name_file_repaired = str_replace(".svg+xml", ".svg", $name_file);
										$array_search[] = $name_file;
										$array_replace[] = $name_file_repaired;
										file_put_contents($test_dir . "/" . $name_file_repaired, $data_file);
									}
								}
								$option = htmlspecialchars_decode(replaceBase($text_option, $base_src . "/"));
								$option = str_replace($array_search, $array_replace, $option);
								$digest = md5($option);
								$option = addslashes($option);

								$order2 = $index_option + 1;

								$sql2 = "INSERT INTO `edu_option` 
								(`question_id`, `content`, `order`, `score`, `time_create`, `member_create`, `time_edit`, `member_edit`) values
								('$question_id', '$option', '$order2', '$score', '$time_create', '$member_create', '$time_edit', '$member_edit'); 
								";

								$database->executeInsert($sql2);
							}
							
						}
					}
				}
			}
			if($is_zip)
			{
				if($dh = opendir($temp_dir))
				{
					unset($file_list);
					while (($file = readdir($dh)) !== false){
						$file_list[] = $temp_dir.'/'.$file;
					}
					closedir($dh);
					foreach($file_list as $file)
					{
						@unlink($file);
					}
				}
				@rmdir(@$temp_dir);
			}
		}
		header("Location: ujian-soal.php?test_id=$test_id");
	}
}
if(isset($_GET['test_id']))
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$edit_key = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$nt = '';
$sql = "SELECT `edu_test`.* $nt,
(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher_id`,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` group by `edu_question`.`test_id`) as `koleksi_question`
from `edu_test` 
where 1
and `edu_test`.`test_id` = '$edit_key' and `school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form action="" method="post" enctype="multipart/form-data" name="form1" id="form1">
  <table width="100%" border="0" class="two-side-table responsive-tow-side-table" cellspacing="0" cellpadding="0">
    <tr>
      <td>Nama</td>
      <td><?php echo $data['name'];?></td>
    </tr>
    <tr>
      <td>Kelas</td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
    </tr>
    <tr>
      <td>Mata Pelajaran</td>
      <td><?php echo $data['subject'];?></td>
    </tr>
    <tr>
      <td>Guru</td>
      <td><?php echo $data['teacher_id'];?></td>
    </tr>
    <tr>
      <td>Keterangan</td>
      <td><?php echo $data['description'];?></td>
    </tr>
    <tr>
      <td>Petunjuk</td>
      <td><?php echo $data['guidance'];?></td>
    </tr>
    <tr>
      <td>Metode Penilaian</td>
      <td><?php if($data['assessment_methods'] == 'H') echo "Nilai Tertinggi"; if($data['assessment_methods'] == 'N') echo "Nilai Terbaru";?></td>
    </tr>
    <tr>
      <td>Jumlah Soal</td>
      <td><?php echo $data['number_of_question'];?></td>
    </tr>
    <tr>
      <td>Koleksi Soal</td>
      <td><?php echo ($data['koleksi_question']);?></td>
    </tr>
    <tr>
      <td>Jumlah Pilihan</td>
      <td><?php echo $data['number_of_option'];?></td>
    </tr>
    <tr>
      <td>Soal Per Halaman</td>
      <td><?php echo $data['question_per_page'];?></td>
    </tr>
    <tr>
      <td>Nilai Standard</td>
      <td><?php echo $data['standard_score'];?></td>
    </tr>
    <tr>
      <td>Penalti</td>
      <td><?php echo $data['penalty'];?></td>
    </tr>
    <tr>
      <td>Aktif</td>
      <td><?php echo $data['active']?'Ya':'Tidak';?></td>
    </tr>
    <tr>
      <td>File Ujian</td>
      <td><input type="file" name="file" id="file" required="required" /> <input type="hidden" name="test_id" value="<?php echo $data['test_id'];?>" /></td>
    </tr>
    <tr>
      <td></td>
      <td><input type="submit" name="import" id="import" class="com-button" value="Impor Soal" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_id=<?php echo $data['test_id'];?>'" />
        <input type="button" name="showall" id="showall" value="Tampilkan Semua" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" /></td>
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
$tahun_id = kh_filter_input(INPUT_GET, 'tahun_id', FILTER_SANITIZE_NUMBER_UINT);
$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);

?>
<script type="text/javascript">
window.onload = function()
{
	$(document).on('change', '#searchform select', function(){
		$(this).closest('form').submit();
	});
}
</script>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Kelas</span> 
    <select class="input-select" name="class_id" id="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php 
	$sql2 = "select * from `edu_class` where `school_id` = '$school_id' ";

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
    <span class="search-label">Ujian</span>
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
$sql_filter .= " and (`edu_test`.`name` like '%".addslashes($pagination->query)."%' )";
}

if($class_id != '')
{
	$sql_filter .= " and concat(',',`edu_test`.`class`,',') like '%,$class_id,%' ";
	$pagination->array_get[] = 'class_id';
}

$nt = '';


$sql = "SELECT `edu_test`.* $nt,
(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher`,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` group by `edu_question`.`test_id`)*1 as `number_of_question`
from `edu_test`
where 1 and `edu_test`.`school_id` = '$school_id' $sql_filter
order by `edu_test`.`test_id` desc
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);

$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit > 0)
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
<?php
$array_class = $picoEdu->getArrayClass($school_id);
?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
	.hide-some-cell tr td:nth-child(7), .hide-some-cell tr td:nth-child(9){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(8)
	{
		display:none;
	}
}
@media screen and (max-width:399px)
{
	.hide-some-cell tr td:nth-child(4), .hide-some-cell tr td:nth-child(5)
	{
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
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></td>
      <td width="25">No</td>
      <td>Nama Ujian</td>
      <td>Kelas</td>
      <td>Mata Pelajaran</td>
      <td>Guru</td>
      <td>Sifat</td>
      <td>Soal</td>
      <td>Aktif</td>
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
    <tr<?php $rowclass=""; if(@$data['default']==1) $rowclass.=" data-default"; if(isset($data['active'])){if(@$data['active']==1) $rowclass.=" data-active"; if(@$data['active']==0) $rowclass.=" data-inactive";} $rowclass = trim($rowclass); if(strlen($rowclass)){echo " class=\"$rowclass\"";}?>>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit&test_id=<?php echo $data['test_id'];?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-edit-16" alt="Edit" border="0" /></a></td>
      <td align="right"><?php echo $no;?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['name'];?></a></td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']); $class_sort = $picoEdu->textClass($array_class, $data['class'], 2);?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class);?>"><?php echo $class_sort;?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo $data['subject'];?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['teacher']);?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=detail&test_id=<?php echo $data['test_id'];?>"><?php echo ($data['open'])?'Terbuka':'Tertutup';?></a></td>
      <td><a href="ujian-soal.php?test_id=<?php echo $data['test_id'];?>"><?php echo $data['number_of_question'];?></a></td>
      <td><?php echo $data['active']?'Ya':'Tidak';?></td>
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
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>
