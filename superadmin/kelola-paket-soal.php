<?php
require_once dirname(__DIR__)."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once __DIR__."/bukan-super-admin.php";
	exit();
}
require_once dirname(__DIR__)."/lib.inc/lib.test.php";

$pageTitle = "Kelola Paket Soal";
$pagination = new \Pico\PicoPagination();
$time_create = $time_edit = $database->getLocalDateTime();


if(@$_GET['option'] == 'export')
{
	$test_collection_id = kh_filter_input(INPUT_GET, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
	$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data3 = $stmt->fetch(\PDO::FETCH_ASSOC);
		$basename = $data3['file_path'];
		$file_path = dirname(__DIR__) . "/media.edu/question-collection/data/".$basename;
		header("Content-Type: text/xml");
		$fn = str_replace(" ", "-", strtolower($data3['name']));
		header("Content-Disposition: attachment; filename=\"$fn.xml\"");
		readfile($file_path);
	}
	exit();
}

if(isset($_POST['sort']))
{
	$test_collection_id = kh_filter_input(INPUT_POST, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
	$array_question = kh_filter_input(INPUT_POST, "array_question", FILTER_SANITIZE_STRING_NEW);
	$new_order = explode(",", $array_question);


	$test_collection_id = kh_filter_input(INPUT_POST, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
	$question_index = kh_filter_input(INPUT_POST, "question_index", FILTER_SANITIZE_NUMBER_UINT);
	$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data3 = $stmt->fetch(\PDO::FETCH_ASSOC);
		$basename = $data3['file_path'];
		$file_path = dirname(__DIR__) . "/media.edu/question-collection/data/".$basename;
		
		$s = file_get_contents($file_path);
		$test_data = simplexml_load_string($s);
		
		$count_question = count($test_data->item);
		$question_array = array();
		for($ii = 0; $ii < $count_question; $ii++)
		{
			$question_array[$ii] = $test_data->item[$ii];
		}
		
		// Stringify begin
		$xml_question = array();
		for($jj = 0; $jj<$count_question; $jj++)
		{
			$key1 = $new_order[$jj];
			$question = $question_array[$key1];
			$xml_question[$key1] = "\r\n<item>".
			"\r\n\t<question>".
			"\r\n\t\t<text>".htmlspecialchars($question->question->text)."</text>".
			"\r\n\t\t<numbering>".htmlspecialchars($question->question->numbering)."</numbering>".
			"\r\n\t\t<competence>".htmlspecialchars($question->question->competence)."</competence>".
			"\r\n\t\t<random>".htmlspecialchars($question->random)."</random>";

			$count_file = count(@$question->question->file);
			if($count_file > 1)
			{
				for($ll = 0; $ll<$count_file; $ll++)
				{
					$xml_question[$key1] .= "\r\n\t\t<file>";
					$val2 = $question->question->file[$ll];
					$xml_question[$key1] .= "\r\n\t\t\t<name>".$val2->name."</name>".
					"\r\n\t\t\t<type>".$val2->type."</type>".
					"\r\n\t\t\t<encoding>".$val2->encoding."</encoding>".
					"\r\n\t\t\t<data>".$val2->data."</data>";
					$xml_question[$key1] .= "\r\n\t\t</file>";
				}
			}
			else if($count_file == 1)
			{
				$xml_question[$key1] .= "\r\n\t\t<file>";
				$xml_question[$key1] .= "\r\n\t\t\t<name>".$question->question->file->name."</name>".
				"\r\n\t\t\t<type>".$question->question->file->type."</type>".
				"\r\n\t\t\t<encoding>".$question->question->file->encoding."</encoding>".
				"\r\n\t\t\t<data>".$question->question->file->data."</data>";
				$xml_question[$key1] .= "\r\n\t\t</file>";
			}
			$xml_question[$key1] .= "\r\n\t</question>";

			$xml_question[$key1] .= "\r\n<answer>";
			
			$count_option = count($question->answer->option);
			for($kk = 0; $kk<$count_option; $kk++)
			{
				$option = $question->answer->option[$kk];
				if(!@$option->score && @$val3->value)
				{
					$option->score = @$option->value;
				}
				$xml_question[$key1] .= 
					"\r\n\t<option>".
					"\r\n\t\t<value>".htmlspecialchars($option->value)."</value>".
					"\r\n\t\t<score>".htmlspecialchars(@$option->score)."</score>".
					"\r\n\t\t<text>".htmlspecialchars($option->text)."</text>";
				
				$count_file = count(@$option->file);	
				if($count_file > 1)
				{
					for($mm = 0; $mm < $count_file; $mm++)
					{
						$val2 = $option->file[$mm];
						$xml_question[$key1] .= "\r\n\t\t<file>";
						$xml_question[$key1] .= "\r\n\t\t\t<name>".$val2->name."</name>".
						"\r\n\t\t\t<type>".$val2->type."</type>".
						"\r\n\t\t\t<encoding>".$val2->encoding."</encoding>".
						"\r\n\t\t\t<data>".$val2->data."</data>";
						$xml_question[$key1] .= "\r\n\t\t</file>";
					}
				}
				else if($count_file == 1)
				{
					$xml_question[$key1] .= "\r\n\t\t<file>";
					$xml_question[$key1] .= "\r\n\t\t\t<name>".$option->file->name."</name>".
					"\r\n\t\t\t<type>".$option->file->type."</type>".
					"\r\n\t\t\t<encoding>".$option->file->encoding."</encoding>".
					"\r\n\t\t\t<data>".$option->file->data."</data>";
					$xml_question[$key1] .= "\r\n\t\t</file>";
				}
				$xml_question[$key1] .= "\r\n\t</option>";
			}
			$xml_question[$key1] .= "\r\n</answer>";
			$xml_question[$key1] .= "\r\n</item>";

		}
		// Stringify end
		
		$xml_data_str = "<"."?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<test>".implode("\r\n", $xml_question)."</test>";
		$fileSync->createFileWithContent($file_path, $xml_data_str, true);
	}

	exit();
}
if(@$_GET['option'] == 'delete')
{
	$test_collection_id = kh_filter_input(INPUT_GET, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
	$question_index = kh_filter_input(INPUT_GET, "question_index", FILTER_SANITIZE_NUMBER_UINT);
	$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data3 = $stmt->fetch(\PDO::FETCH_ASSOC);
		$basename = $data3['file_path'];
		$file_path = dirname(__DIR__) . "/media.edu/question-collection/data/".$basename;
		
		$s = file_get_contents($file_path);
		$test_data = simplexml_load_string($s);
		
		$count_question = count($test_data->item);
		$question_array = array();
		for($ii = 0; $ii < $count_question; $ii++)
		{
			$question_array[$ii] = $test_data->item[$ii];
		}
		
		
		// Stringify begin
		$xml_question = array();
		for($jj = 0; $jj<$count_question; $jj++)
		{
			if($jj == $question_index)
			{
				continue;
			}
			$key1 = $jj;
			$question = $question_array[$key1];
			$xml_question[$key1] = "\r\n<item>".
			"\r\n\t<question>".
			"\r\n\t\t<text>".htmlspecialchars($question->question->text)."</text>".
			"\r\n\t\t<numbering>".htmlspecialchars($question->question->numbering)."</numbering>".
			"\r\n\t\t<competence>".htmlspecialchars($question->question->competence)."</competence>".
			"\r\n\t\t<random>".htmlspecialchars($question->random)."</random>";
			$count_file = count(@$question->question->file);
			if($count_file > 1)
			{
				for($ll = 0; $ll<$count_file; $ll++)
				{
					$xml_question[$key1] .= "\r\n\t\t<file>";
					$val2 = $question->question->file[$ll];
					$xml_question[$key1] .= "\r\n\t\t\t<name>".$val2->name."</name>".
					"\r\n\t\t\t<type>".$val2->type."</type>".
					"\r\n\t\t\t<encoding>".$val2->encoding."</encoding>".
					"\r\n\t\t\t<data>".$val2->data."</data>";
					$xml_question[$key1] .= "\r\n\t\t</file>";
				}
			}
			else if($count_file == 1)
			{
				$xml_question[$key1] .= "\r\n\t\t<file>";
				$xml_question[$key1] .= "\r\n\t\t\t<name>".$question->question->file->name."</name>".
				"\r\n\t\t\t<type>".$question->question->file->type."</type>".
				"\r\n\t\t\t<encoding>".$question->question->file->encoding."</encoding>".
				"\r\n\t\t\t<data>".$question->question->file->data."</data>";
				$xml_question[$key1] .= "\r\n\t\t</file>";
			}
			$xml_question[$key1] .= "\r\n\t</question>";

			$xml_question[$key1] .= "\r\n<answer>";
			
			$count_option = count($question->answer->option);
			for($kk = 0; $kk<$count_option; $kk++)
			{
				$option = $question->answer->option[$kk];
				if(!@$option->score && @$val3->value)
				{
					$option->score = @$option->value;
				}
				$xml_question[$key1] .= 
					"\r\n\t<option>".
					"\r\n\t\t<value>".htmlspecialchars($option->value)."</value>".
					"\r\n\t\t<score>".htmlspecialchars(@$option->score)."</score>".
					"\r\n\t\t<text>".htmlspecialchars($option->text)."</text>";
						
				$count_file = count(@$option->file);	
				if($count_file > 1)
				{
					for($mm = 0; $mm < $count_file; $mm++)
					{
						$val2 = $option->file[$mm];
						$xml_question[$key1] .= "\r\n\t\t<file>";
						$xml_question[$key1] .= "\r\n\t\t\t<name>".$val2->name."</name>".
						"\r\n\t\t\t<type>".$val2->type."</type>".
						"\r\n\t\t\t<encoding>".$val2->encoding."</encoding>".
						"\r\n\t\t\t<data>".$val2->data."</data>";
						$xml_question[$key1] .= "\r\n\t\t</file>";
					}
				}
				else if($count_file == 1)
				{
					$xml_question[$key1] .= "\r\n\t\t<file>";
					$xml_question[$key1] .= "\r\n\t\t\t<name>".$option->file->name."</name>".
					"\r\n\t\t\t<type>".$option->file->type."</type>".
					"\r\n\t\t\t<encoding>".$option->file->encoding."</encoding>".
					"\r\n\t\t\t<data>".$option->file->data."</data>";
					$xml_question[$key1] .= "\r\n\t\t</file>";
				}
				$xml_question[$key1] .= "\r\n\t</option>";
			}
			$xml_question[$key1] .= "\r\n</answer>";
			$xml_question[$key1] .= "\r\n</item>";

		}
		// Stringify end
		
		$xml_data_str = "<"."?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<test>".implode("\r\n", $xml_question)."</test>";
		$fileSync->createFileWithContent($file_path, $xml_data_str, true);
	}
	header("Location: ".$picoEdu->gateBaseSelfName()."?option=edit&test_collection_id=$test_collection_id");
	exit();
}
if(isset($_POST['save']) && (@$_GET['option'] == 'edit' || @$_GET['option'] == 'add'))
{
	
	$test_collection_id = kh_filter_input(INPUT_POST, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
	$question_index = kh_filter_input(INPUT_POST, "question_index", FILTER_SANITIZE_NUMBER_UINT);
	$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data3 = $stmt->fetch(\PDO::FETCH_ASSOC);
		$basename = $data3['file_path'];
		$file_path = dirname(__DIR__) . "/media.edu/question-collection/data/".$basename;
		
		$s = file_get_contents($file_path);
		$test_data = simplexml_load_string($s);
		
		$count_question = count($test_data->item);
		$question_array = array();
		for($ii = 0; $ii < $count_question; $ii++)
		{
			$question_array[$ii] = $test_data->item[$ii];
		}
		$question_text = kh_filter_input(INPUT_POST, "question");
		$question_text = utf8ToEntities($question_text);
		$numbering = kh_filter_input(INPUT_POST, "numbering", FILTER_SANITIZE_STRING_NEW);
		$competence = trim(kh_filter_input(INPUT_POST, "basic_competence", FILTER_SANITIZE_STRING_NEW));
		$random = kh_filter_input(INPUT_POST, "random", FILTER_SANITIZE_NUMBER_UINT);
		$number_of_option = kh_filter_input(INPUT_POST, "number_of_option", FILTER_SANITIZE_NUMBER_UINT);
		
		if(!isset($question_array[$question_index]))
		{
			$question_array[$question_index] = new StdClass();
			$question_array[$question_index]->question = new StdClass();
			$question_array[$question_index]->answer = new StdClass();
			$question_array[$question_index]->answer->option = array();
		}
		
		$question_array[$question_index]->question->text = ($question_text);
		$question_array[$question_index]->question->numbering = ($numbering);
		$question_array[$question_index]->question->competence = ($competence);
		$question_array[$question_index]->question->random = ($random);
		
		for($jj = 0; $jj<$number_of_option; $jj++)
		{
			$score = @$_POST['score'][$jj] * 1;
			$text = (@$_POST['option'][$jj]);
			$question_array[$question_index]->answer->option[$jj]->value = $score;
			$question_array[$question_index]->answer->option[$jj]->score = $score;
			$question_array[$question_index]->answer->option[$jj]->text = ($text);
		}
		
		// Stringify begin
		$xml_question = array();
		for($jj = 0; $jj<$count_question; $jj++)
		{
			$key1 = $jj;
			$question = $question_array[$key1];
			$xml_question[$key1] = "\r\n<item>".
			"\r\n\t<question>".
			"\r\n\t\t<text>".htmlspecialchars($question->question->text)."</text>".
			"\r\n\t\t<numbering>".htmlspecialchars($question->question->numbering)."</numbering>".
			"\r\n\t\t<competence>".htmlspecialchars($question->question->competence)."</competence>".
			"\r\n\t\t<random>".htmlspecialchars($question->random)."</random>";
			$count_file = count(@$question->question->file);
			if($count_file > 1)
			{
				for($ll = 0; $ll<$count_file; $ll++)
				{
					$xml_question[$key1] .= "\r\n\t\t<file>";
					$val2 = $question->question->file[$ll];
					$xml_question[$key1] .= "\r\n\t\t\t<name>".$val2->name."</name>".
					"\r\n\t\t\t<type>".$val2->type."</type>".
					"\r\n\t\t\t<encoding>".$val2->encoding."</encoding>".
					"\r\n\t\t\t<data>".$val2->data."</data>";
					$xml_question[$key1] .= "\r\n\t\t</file>";
				}
			}
			else if($count_file == 1)
			{
				$xml_question[$key1] .= "\r\n\t\t<file>";
				$xml_question[$key1] .= "\r\n\t\t\t<name>".$question->question->file->name."</name>".
				"\r\n\t\t\t<type>".$question->question->file->type."</type>".
				"\r\n\t\t\t<encoding>".$question->question->file->encoding."</encoding>".
				"\r\n\t\t\t<data>".$question->question->file->data."</data>";
				$xml_question[$key1] .= "\r\n\t\t</file>";
			}
			$xml_question[$key1] .= "\r\n\t</question>";

			$xml_question[$key1] .= "\r\n<answer>";
			
			$count_option = count($question->answer->option);
			for($kk = 0; $kk<$count_option; $kk++)
			{
				$option = $question->answer->option[$kk];
				if(!@$option->score && @$val3->value)
				{
					$option->score = @$option->value;
				}
				$xml_question[$key1] .= 
					"\r\n\t<option>".
					"\r\n\t\t<value>".htmlspecialchars($option->value)."</value>".
					"\r\n\t\t<score>".htmlspecialchars(@$option->score)."</score>".
					"\r\n\t\t<text>".htmlspecialchars($option->text)."</text>";
						
				$count_file = count(@$option->file);	
				if($count_file > 1)
				{
					for($mm = 0; $mm < $count_file; $mm++)
					{
						$val2 = $option->file[$mm];
						$xml_question[$key1] .= "\r\n\t\t<file>";
						$xml_question[$key1] .= "\r\n\t\t\t<name>".$val2->name."</name>".
						"\r\n\t\t\t<type>".$val2->type."</type>".
						"\r\n\t\t\t<encoding>".$val2->encoding."</encoding>".
						"\r\n\t\t\t<data>".$val2->data."</data>";
						$xml_question[$key1] .= "\r\n\t\t</file>";
					}
				}
				else if($count_file == 1)
				{
					$xml_question[$key1] .= "\r\n\t\t<file>";
					$xml_question[$key1] .= "\r\n\t\t\t<name>".$option->file->name."</name>".
					"\r\n\t\t\t<type>".$option->file->type."</type>".
					"\r\n\t\t\t<encoding>".$option->file->encoding."</encoding>".
					"\r\n\t\t\t<data>".$option->file->data."</data>";
					$xml_question[$key1] .= "\r\n\t\t</file>";
				}
				$xml_question[$key1] .= "\r\n\t</option>";
			}
			$xml_question[$key1] .= "\r\n</answer>";
			$xml_question[$key1] .= "\r\n</item>";

		}
		// Stringify end
		
		$xml_data_str = "<"."?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<test>".implode("\r\n", $xml_question)."</test>";
		$fileSync->createFileWithContent($file_path, $xml_data_str, true);
	}
	header("Location: ".$picoEdu->gateBaseSelfName()."?option=edit&test_collection_id=$test_collection_id");
	exit();
}

if(isset($_POST['set_active']) && isset($_POST['test_collection_id']))
{
	$test_id = $_POST['test_collection_id'];
	foreach($test_id as $key=>$val)
	{
		$test_collection_id = addslashes($val);
		$sql = "UPDATE `edu_test_collection` SET `active` = true WHERE `test_collection_id` = '$test_collection_id' ";
		$database->executeUpdate($sql, true);
	}
	header("Location: ".basename($_SERVER['REQUEST_URI']));
	exit();
}
if(isset($_POST['set_inactive']) && isset($_POST['test_collection_id']))
{
	$test_id = $_POST['test_collection_id'];
	foreach($test_id as $key=>$val)
	{
		$test_collection_id = addslashes($val);
		$sql = "UPDATE `edu_test_collection` SET `active` = false WHERE `test_collection_id` = '$test_collection_id' ";
		$database->executeUpdate($sql, true);
	}
	header("Location: ".basename($_SERVER['REQUEST_URI']));
	exit();
}

if(@$_GET['option'] == 'edit' || @$_GET['option'] == 'add')
{
if(isset($_GET['test_collection_id']) && (@$_GET['option'] == 'add' || isset($_GET['question_index'])))
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
$test_collection_id = kh_filter_input(INPUT_GET, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
$question_index = kh_filter_input(INPUT_GET, "question_index", FILTER_SANITIZE_NUMBER_UINT);
$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id' ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{

$data3 = $stmt->fetch(\PDO::FETCH_ASSOC);
$basename = $data3['file_path'];
$file_path = dirname(__DIR__) . "/media.edu/question-collection/data/".$basename;

$s = file_get_contents($file_path);
$test_data = simplexml_load_string($s);



$files = array();
$questions = array();
$options = array();


$data3['collection'] = count($test_data->item);
//$data3['standard_score'] = 0;
//$data3['number_of_option'] = 0;

$sort_order = 0;
$nquestion = 0;
$noption = 0;
$question_count = $nquestion = count($test_data->item);


$number_of_option = kh_filter_input(INPUT_POST, "number_of_option", FILTER_SANITIZE_NUMBER_UINT);
$numbering = kh_filter_input(INPUT_POST, "numbering", FILTER_SANITIZE_STRING_NEW);
$competence = trim(kh_filter_input(INPUT_POST, "basic_competence", FILTER_SANITIZE_STRING_NEW));
$random = kh_filter_input(INPUT_POST, "random", FILTER_SANITIZE_NUMBER_UINT);
$question_text = kh_filter_input(INPUT_POST, "question");
$question_text = utf8ToEntities($question_text);
$question_text = htmlspecialchars($question_text);
	

for($ii = 0; $ii < $question_count; $ii++)
{
	$question = $test_data->item[$ii];
	
	if($ii == $question_index)
	{
		$xml_item = "";
		$xml_item .= "<item>\r\n";
		$xml_item .= "<question>\r\n";
		$xml_item .= "<text>\r\n";
		$xml_item .= $question_text;
		$xml_item .= "</text>\r\n";
		$xml_item .= "<random>\r\n";
		$xml_item .= $random;
		$xml_item .= "</random>\r\n";
		$xml_item .= "<numbering>\r\n";
		$xml_item .= $numbering;
		$xml_item .= "</numbering>\r\n";
		$xml_item .= "<competence>\r\n";
		$xml_item .= $competence;
		$xml_item .= "</competence>\r\n";
		$xml_item .= "</question>\r\n";
		$xml_item .= "</item>\r\n";
	}	
	
}


if($question_index >= $nquestion || @$_GET['option'] == 'add')
{
	$question_index = $nquestion; 
}
if($question_index == $nquestion)
{
	$data = array('text'=>'', 'numbering'=>'upper-alpha', 'random'=>'1');
	$data_options = array();
	for($x = 0; $x<$noption; $x++)
	{
		$data_options[$x] = array('text'=>'', 'value'=>'0');
	}
}
else
{
	$question_data = $test_data->item[$question_index];
	$data = $question_data->question;
	$data_options = $question_data->answer->option;
}
?>
<link rel="stylesheet" type="text/css" href="../lib.assets/theme/default/css/test.css" />
<script type="text/javascript" src="../lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
var numbering = <?php echo json_encode(\Pico\PicoConst::NUMBERING_TYPE);?>;
var test_collection_id = '<?php echo $data3['test_collection_id'];?>';
var baseTestURLLength = <?php echo strlen("media.edu/school/$school_id/test/$data3[test_collection_id]/");?>;	

function basename(path) {
return path.replace(/\\/g,'/').replace(/.*\//, '');
}
function dirname(path) {
return path.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
}
function getfileExtension(filename){
return (/[.]/.exec(filename)) ? /[^.]+$/.exec(filename):'';
}
function removefileextension(filename){
return filename.replace(/\.[^/.]+$/, "");
}
var ascii_svg_server = 'lib.tools/asciisvg/svgimg.php';
var equation_preview_url = '../../../../../../cgi-bin/equgen.cgi?' ;
var equation_generator_url = '../../../../../../equgen.php?' ;
var equation_renderer_machine = (navigator.userAgent.toString().indexOf('Firefox') > -1)?'mathml-png':'mathjax-svg';
var quran_server = '../lib.quran';
$().ready(function() {
	$('textarea.htmleditor').tinymce({
		// Location of TinyMCE script
		script_url : '../lib.assets/script/tiny_mce/tiny_mce.js',

		// General options
		theme : "advanced",
        ascii_svg_server : ascii_svg_server,        
		equation_preview_url : equation_preview_url,        
		equation_generator_url : equation_generator_url, 
        equation_renderer_machine : equation_renderer_machine, 
		quran_server : quran_server, 
        ascii_svg_server : ascii_svg_server,
		noneditable_editable_class: "is-editable",
  		noneditable_noneditable_class: "is-locked",
		plugins : "noneditable,autolink,lists,style,table,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist,quran,latex,equation,chem,asciisvg,chart,draw",
		theme_advanced_buttons1:"pasteword,pastetext,undo,redo,search,bold,italic,underline,strikethrough,formatselect,fontselect,fontsizeselect,justifyleft,justifycenter,justifyright,justifyfull,ltr,rtl,numlist,bullist,indent,outdent,blockquote",
		theme_advanced_buttons2:"visualaid,forecolor,backcolor,removeformat,anchor,link,unlink,image,media,quran,charmap,sup,sub,latex,equation,chem,asciisvg,chart,draw,hr,table,row_props,cell_props,col_after,col_before,row_after,row_before,merge_cells,split_cells,delete_col,delete_row,delete_table,quran,arabiceditor,code,preview",
		theme_advanced_buttons3:"",
		theme_advanced_buttons4:"",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal:false,
		extended_valid_elements : "iframe[style|src|title|width|height|allowfullscreen|frameborder]",

		// Example content CSS (should be your site CSS)
		content_css : "lib.assets/theme/default/css/content.min.css",
		
		
		apply_source_formatting:true,
		accessibility_warnings:false,

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Replace values for the template plugin
		template_replace_values : {
			username : "Kamshory",
			staffid : "-"
		}
	});
	setTimeout(function(){
		$('textarea.htmleditor').each(function(index, element) {
			var id = $(this).attr('id');
			var iframe = document.getElementById(id+'_ifr');
			var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
			// innerDoc.addEventListener('paste', pasteHandler);
            
        });
	}, 2000);
	$(document).on('change', '#numbering', function(){
		var val = $(this).val();
		$('.option-item').each(function(index, element) {
            var idx = parseInt($(this).attr('data-index'));
			var label = numbering[val][idx];
			$(this).find('.option-label').text(label);
        });
	});
	$(document).on('click', '#split', function(){
		$('#split-dialog').dialog({
			modal:true,
			title:'Split Jawaban'
		});
	});
});
	
function pasteHandler(e)
{
	var cbData;
	if(e.clipboardData) 
	{
		cbData = e.clipboardData;
	}
	else if(window.clipboardData)
	{
		cbData = window.clipboardData;
	}
	if(e.msConvertURL)
	{
		var fileList = cbData.files;
		if(fileList.length > 0)
		{
			for(var i = 0; i < fileList.length; i++)
			{
				var blob = fileList[i];
				readPastedBlob(blob);
			}
		}
	}
	if(cbData && cbData.items)
	{
		if((text = cbData.getData("text/plain")))
		{
			return;
		}
		for(var i = 0; i < cbData.items.length; i++)
		{
			if(cbData.items[i].type.indexOf('image') !== -1)
			{
				var blob = cbData.items[i].getAsFile();
				readPastedBlob(blob);
			}
		}
	}
	function readPastedBlob(blob)
	{
		if(blob)
		{
			reader = new FileReader();
			reader.onload = function(evt)
			{
				pasteImage(evt.target.result);
			};
			reader.readAsDataURL(blob);
		}
	}
	function pasteImage(source)
	{
		if(window.navigator.userAgent.toString().indexOf('Firefox') == -1)
		{
			var image = "<img src='" + source + "' data-mce-selected='1'></img>";
			window.tinyMCE.execCommand('mceInsertContent', false, image);
		}
	}
}

function fileBrowserCallBack(field_name, url, type, win){
	if(url.indexOf('data:') != -1)
	{
		url = '';
	}
	url = url.substr(baseTestURLLength);
	var ajaxFilemanagerURL = "../lib.tools/filemanager/?test_collection_id="+test_collection_id+"&editor=tiny_mce&type="+type+"&field_name="+field_name+'&dir=base/'+dirname(url);
	switch (type){
		case "image":break;
		case "media":break;
		case "flash":break;
		case "file":break;
		default:
		return false;
	}
	tinyMCE.activeEditor.windowManager.open({url:ajaxFilemanagerURL,width:780,height:440,resizable:true,maximizable:true,inline:"yes",close_previous:"no"},{window:win,input:field_name});
}
</script>

<div class="dialogs">
	<div id="split-dialog">
    	<div id="split-dialog-inner">
        	<div class="content-editable" contenteditable="true">
            </div>
        </div>
    </div>
</div>

<?php
if(isset($data->file))
{
	$content = "";
	if(isset($data->file[0]))
	{
		foreach($data->file as $key=>$val)
		{
			$name = $val->name;
			$type = $val->type;
			$encoding = $val->encoding;
			$search = ' src="'.$name.'"';
			$replace = 'data:'.$type.';'.$encoding.','.$val->data;
			$data->text = str_ireplace($search, ' src="'.$replace.$content.'"', $data->text);
		}
	}
	else if(isset($data->file->name))
	{
		$name = $data->file->name;
		$type = $data->file->type;
		$encoding = $data->file->encoding;
		$search = ' src="'.$name.'"';
		$replace = 'data:'.$type.';'.$encoding.','.$data->file->data;
		$data->text = str_ireplace($search, ' src="'.$replace.$content.'"', $data->text);
	}
}
?>

<form id="form2" name="form2" method="post" action="">
<div class="test-info">
<table width="100%" border="0">
  <tr>
    <td width="160">Nama Ujian</td>
    <td><?php echo $data3['name'];?> </td>
  </tr>
  <tr>
    <td>Koleksi Soal</td>
    <td><?php echo $data3['collection'];?> soal <a href="kelola-paket-soal.php?test_collection_id=<?php echo $data3['test_collection_id'];?>">Lihat</a></td>
  </tr>
  <tr>
    <td>Jumlah Pilihan</td>
    <td><?php echo $data3['number_of_option'];?> pilihan</td>
  </tr>
</table>
 </div>
<div class="question-area">
<fieldset>
<legend>Soal Ujian</legend>
<div class="question-prop">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="160">Kompetensi Dasar</td>
    <td><input type="text" class="input-text input-text-short" name="basic_competence" id="basic_competence" value="<?php echo @$data->competence;?>" /></td>
  </tr>
  <tr>
    <td>Tipe Pilihan</td>
    <td><select name="numbering" id="numbering" data-required="true" required="required">
	<?php echo $picoEdu->selectOptionNumbering($data->numbering);?>
    </select></td>
  </tr>
  <tr>
    <td>Pengacakan Pilihan</td>
    <td><label><input type="checkbox" name="random" id="random" value="1"<?php echo $picoEdu->trueFalse(isset($data->random) && $data->random, \Pico\PicoConst::INPUT_CHECKBOX_CHECKED, '');?> /> Diacak</label></td>
  </tr>
</table>
</div>
<div class="question-editor">
<textarea spellcheck="false" class="htmleditor" name="question" id="question" style="width:100%;"><?php echo $picoEdu->trueFalse(isset($data->text), htmlspecialchars($data->text), '');?></textarea>
<input type="hidden" name="question_index" id="question_index" value="<?php echo $question_index;?>" />
</div>
</fieldset>
</div>
<div class="option-area">
<fieldset>
<legend>Pilihan Jawaban</legend>

<?php

if(!isset($data3['standard_score']))
{
	$data3['standard_score'] = 1;
}

$numbering = $picoEdu->trimWhitespace($data->numbering);
$i = 0;
$count_option = count($data_options);

for($jj = 0; $jj < $count_option; $jj++)
{
	$data2 = $data_options[$jj];
	if(@$data2->value > 0 && @$data2->value > $data3['standard_score'])
	{
		$data3['standard_score'] = $data2->value;
	}
	if(@$data2->score > 0 && @$data2->score > $data3['standard_score'])
	{
		$data3['standard_score'] = $data2->score;
	}
}
for($jj = 0; $jj < $count_option; $jj++)
{
	$data2 = $data_options[$jj];
	if(isset($data2->file))
	{
		if(isset($data2->file[0]))
		{
			foreach($data2->file as $key=>$val)
			{
				$name = $val->name;
				$type = $val->type;
				$encoding = $val->encoding;
				$search = ' src="'.$name.'"';
				$replace = 'data:'.$type.';'.$encoding.','.$val->data;
				$data2->text = str_ireplace($search, ' src="'.$replace.$content.'"', $data2->text);
			}
		}
		else if(isset($data2->file->name))
		{
			$name = $data2->file->name;
			$type = $data2->file->type;
			$encoding = $data2->file->encoding;
			$search = ' src="'.$name.'"';
			$replace = 'data:'.$type.';'.$encoding.','.$data2->file->data;
			$data2->text = str_ireplace($search, ' src="'.$replace.$content.'"', $data2->text);
		}
	}
?>
<div class="option-item" data-index="<?php echo $i;?>">
<div class="option-score">Pilihan <span class="option-label"><?php echo \Pico\PicoConst::NUMBERING_TYPE[$numbering][$i];?></span> | Nilai 
<input type="number" min="0" max="<?php echo $data3['standard_score'];?>" class="input-text input-text-short" name="score[<?php echo $i;?>]" id="score_<?php echo $i;?>" value="<?php echo $data2->value;?>" autocomplete="off" /> (Nilai Maksimum <?php echo $data3['standard_score'];?>)</div>
<div class="option-editor">
<textarea spellcheck="false" class="htmleditor" name="option[<?php echo $i;?>]" id="option_<?php echo $i;?>" style="width:100%;"><?php echo htmlspecialchars(($data2->text));?></textarea>
</div>
</div>
<?php
$i++;
}
?>
</fieldset>
</div>

<div class="button-area">
	<input type="hidden" name="test_collection_id" id="test_collection_id" value="<?php echo $test_collection_id;?>" />
	<input type="submit" name="save" id="save" class="btn btn-success" value="Simpan" />
	<input type="button" name="showall" id="showall" class="btn btn-success" value="Tampilkan Semua Soal" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?test_collection_id=<?php echo $test_collection_id;?>'" />
	<input type="hidden" name="number_of_option" value="<?php echo $count_option;?>" />
</div>

</form>
<?php
}
else
{
?>
<div class="alert alert-warning">Ujian tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>">Klik di sini untuk kembali.</a></div>
<?php
}

require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}
else if(isset($_GET['test_collection_id']))
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
$test_collection_id = kh_filter_input(INPUT_GET, "test_collection_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT * FROM `edu_test_collection` WHERE `test_collection_id` = '$test_collection_id'  ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(\PDO::FETCH_ASSOC);
	$basename = $data['file_path'];
	$file_path = dirname(__DIR__) . "/media.edu/question-collection/data/".$basename;
	if(file_exists($file_path))
	{
		$picoTest = new \Pico\PicoTestCreator();
		$text_all = $picoTest->loadXmlData($file_path);

		?>
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css" />
        
        
        <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery.ui.touch-punch.js"></script>
        <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.vendors/sortable/sortable.js"></script>
        <script type="text/javascript">
        
        // Background
        document.addEventListener("DOMContentLoaded", function () {
            function setNoiseBackground(el, width, height, opacity) {
                var canvas = document.createElement("canvas");
                var context = canvas.getContext("2d");       
                canvas.width = width;
                canvas.height = height;       
                for (var i = 0; i < width; i++) {
                    for (var j = 0; j < height; j++) {
                        var val = Math.floor(Math.random() * 255);
                        context.fillStyle = "rgba(" + val + "," + val + "," + val + "," + opacity + ")";
                        context.fillRect(i, j, 1, 1);
                    }
                }       
                el.style.background = "url(" + canvas.toDataURL("image/png") + ")";
            }       
            setNoiseBackground(document.getElementsByTagName('body')[0], 50, 50, 0.02);
        }, false);
        
        function activateSortOrder()
        {		
			Sortable.create(sortable, {
				nimation: 100,
				onChange:function(evt){
					var array_question = [];
                    $("#sortable > li").each(function(index, element) {
                    array_question.push($(this).attr('data-question-index'));
                    });
					$.post('kelola-paket-soal.php', {array_question:array_question.join(','), sort:'yes',test_collection_id:test_collection_id}, function(answer){
						console.log(answer);
						var idx = 0;
						$("#sortable > li").each(function(index, element) {
							$(this).attr('data-question-index', idx);
							idx++;
						});
                    });
				}
			});
        }
        window.onload=function(){
			$('.test-question').attr('id', 'sortable');
            $('.deletequestion').click(function(){
                return confirm('Apakah Anda akan menghapus soal ini beserta dengan seluruh pilihannya?');
            });
			$('.test-question > li').each(function(index, element) {
              	$(this).prepend('\r\n'+  
				'<div class="question-edit-ctrl">\r\n'+ 
				'<a class="btn btn-primary editquestion" href="#">Ubah Soal</a>\r\n'+ 
				'<a class="btn btn-danger deletequestion" href="#">Hapus Soal</a>\r\n'+ 
				'</div>\r\n');
            });
			$(document).on('click', '.editquestion', function(e){
				var index = $(this).closest('li').attr('data-question-index');
				window.location = 'kelola-paket-soal.php?option=edit&test_collection_id='+test_collection_id+'&question_index='+index;
				e.preventDefault();
			});
			$(document).on('click', '.deletequestion', function(e){
				var index = $(this).closest('li').attr('data-question-index');
				if(confirm('Apakah Anda akan menghapus soal ini?'))
				{
					window.location = 'kelola-paket-soal.php?option=delete&test_collection_id='+test_collection_id+'&question_index='+index;
				}
				e.preventDefault();
			});       
        }
		var test_collection_id = <?php echo $test_collection_id;?>;
        </script>
        <style type="text/css">
		
		.title h3 {
			font-size: 22px;
			font-weight: normal;
			text-transform: uppercase;
			margin: 0;
			padding: 2px 0;
			text-align: center;
		}		
		.title h4 {
			font-size: 18px;
			text-align: center;
			font-weight: normal;
			margin: 0 0 10px 0;
			padding: 2px 0 10px 0;
			text-transform: uppercase;
			border-bottom: 1px solid #C5C5C5;
		}		
        </style>
        <div class="title">
        	<h3><?php echo $data['name'];?></h3>
        	<h4><?php 
			echo $picoEdu->getGradeName($data['grade_id']);
			?></h4>
        </div>
        <div id="test-preview">
        <div class="question-text-area">
        <?php
		echo $text_all;
		?>
        </div>
        </div>
        <div class="button-area">
        <input type="button" name="urutkan_soal" id="urutkan_soal" class="btn btn-success" value="Urutkan Soal" onclick="activateSortOrder()" />
        <input type="button" name="export" id="export" class="btn btn-success" value="Ekspor Soal" onclick="window.location='kelola-paket-soal.php?option=export&test_collection_id=<?php echo $test_collection_id;?>'" />
        <input type="button" name="add" id="add" class="btn btn-success" value="Tambah Soal" onclick="window.location='kelola-paket-soal.php?option=add&test_collection_id=<?php echo $test_collection_id;?>'" />
        </div>
        <?php
	}
}
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}
}
else
{
require_once __DIR__."/lib.inc/header.php"; //NOSONAR
$grade_id = kh_filter_input(INPUT_GET, "grade_id", FILTER_SANITIZE_NUMBER_INT);
?>
<style type="text/css">
#test-preview h3 {
    text-align: center;
    font-size: 22px;
    font-weight: normal;
    text-transform: uppercase;
	margin:0;
	padding:4px 0;
}
#test-preview h4 {
    text-align: center;
    font-size: 18px;
    font-weight: normal;
    text-transform: uppercase;
	margin:0 0 10px 0;
	padding:4px 0;
}
.test-question li .option-item p:first-child{
	display:inline-block;
}
.test-question li .option-item div:first-child{
	display:inline-block;
}
</style>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
<script type="text/javascript">
$(document).ready(function(e) {
	$(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
    $(document).on('click', '.load-collection', function(e){
		var id = $(this).attr('data-collection-id');
		$.get('../admin/ajax-preview-question-store.php', {id:id}, function(answer){
			var html = '<div id="test-preview" style="width:900px; height:400px; overflow:auto; position:relative;">'+answer+'</div>';
			overlayDialog(html, 900, 400);
		});
		e.preventDefault();
	});
    $(document).on('click', '.load-word, .load-key', function(e){
		var id = $(this).attr('data-collection-id');
		var data = {id:id};
		if($(this).hasClass('load-key'))
		{
			data.key = 1;
		}
		else
		{
			data.key = 0;
		}
		$.get('../admin/ajax-preview-question-store-word.php', data, function(answer){
			var doc = $('<div>'+answer+'</div>');
			var title = doc.find('.test-header h3').text().trim();
			doc.find('.test-header h3, .test-header h4').css({'text-align':'center'});
			if(title == '')
			{
				title = 'test';
			}
			var content = doc.html(); 
			var style = '<style type="text/css">body{font-family:"Times New Roman", Times, serif; font-size:16px; position:relative;} table[border="1"]{border-collapse:collapse; box-sizing:border-box; max-width:100%;} table[border="1"] td{padding:4px 5px;} table[border="0"] td{padding:4px 0;} p, li{line-height:1.5;} a{color:#000000; text-decoration:none;} h1{font-size:30px;} h2{font-size:26px;} h3{font-size:22px;} h4{font-size:16px;}</style>';
			content = '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>'+title+'</title>'+style+'</head><body style="position:relative;">'+content+'</body></html>';
			var converted = new Blob([content], {type:'text/html'});
			saveAs(converted, title+'.html');
		});
		e.preventDefault();
	});
});
</script>

<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Tingkat</span>
    <select class="form-control input-select" name="grade_id" id="grade_id">
    <option value=""></option>
	<?php
	echo $picoEdu->createGradeOption($grade_id);
	?>
    </select>
    <span class="search-label">Nama</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
  <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";
if($pagination->getQuery()){
	$pagination->appendQueryName('q');
	$sql_filter .= " AND (`edu_test_collection`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}

if($grade_id){
	$pagination->appendQueryName('grade_id');
	$sql_filter .= " AND (`edu_test_collection`.`grade_id` = '$grade_id' )";
}

$nt = '';

$sql = "SELECT `edu_test_collection`.* $nt
FROM `edu_test_collection`
WHERE (1=1) $sql_filter
ORDER BY `edu_test_collection`.`test_collection_id` DESC
";
$sql_test = "SELECT `edu_test_collection`.*
FROM `edu_test_collection`
WHERE (1=1) $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{
$pagination->createPagination($picoEdu->gateBaseSelfName(), true); 
$paginationHTML = $pagination->buildHTML();
?>
<style type="text/css">
@media screen and (max-width:799px)
{
	.hide-some-cell tr td:nth-child(9), .hide-some-cell tr td:nth-child(11), .hide-some-cell tr td:nth-child(13){
		display:none;
	}
}
@media screen and (max-width:599px)
{
	.hide-some-cell tr td:nth-child(8), .hide-some-cell tr td:nth-child(10), .hide-some-cell tr td:nth-child(12), .hide-some-cell tr td:nth-child(14), .hide-some-cell tr td:nth-child(15){
		display:none;
	}
}
</style>
<form name="form1" method="post" action="">
<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-test_collection_id" id="control-test_collection_id" class="checkbox-selector" data-target=".test_collection_id" value="1"></td>
      <td width="16"><i class="fas fa-list"></i></td>
      <td width="16"><img src="<?php echo $cfg->base_assets;?>lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Browse" border="0" /></td>
      <td width="16"><img src="<?php echo $cfg->base_assets;?>lib.tools/images/trans.gif" class="icon-16 icon-download-16" alt="Download" border="0" /></td>
      <td width="16"><img src="<?php echo $cfg->base_assets;?>lib.tools/images/trans.gif" class="icon-16 icon-key-16" alt="Key" border="0" /></td>
      <td width="25">No</td>
      <td>Name</td>
      <td>Tingkat</td>
      <td>Nama File</td>
      <td>Ukuran</td>
      <td>Diambil</td>
      <td>Soal</td>
      <td>Pilihan</td>
      <td>Aktif</td>
</tr>
    </thead>
    <tbody>
    <?php
	$no = $pagination->getOffset();
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{
	$no++;
	?>
    <tr class="<?php echo $picoEdu->getRowClass($data);?>">
      <td><input type="checkbox" name="test_collection_id[]" id="test_collection_id" value="<?php echo $data['test_collection_id'];?>" class="test_collection_id" /></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&test_collection_id=<?php echo $data['test_collection_id'];?>"><i class="fas fa-list"></i></a></td>
      <td><a class="load-collection" data-collection-id="<?php echo $data['test_collection_id'];?>" href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><img src="<?php echo $cfg->base_assets;?>lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Browse" border="0" /></a></td>
      <td><a class="load-word" data-collection-id="<?php echo $data['test_collection_id'];?>" href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><img src="<?php echo $cfg->base_assets;?>lib.tools/images/trans.gif" class="icon-16 icon-download-16" alt="Download" border="0" /></a></td>
      <td><a class="load-key" data-collection-id="<?php echo $data['test_collection_id'];?>" href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>&key=1"><img src="<?php echo $cfg->base_assets;?>lib.tools/images/trans.gif" class="icon-16 icon-key-16" alt="Key" border="0" /></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['grade_id'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_name'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['file_size'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['taken'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['number_of_question'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&test_collection_id=<?php echo $data['test_collection_id'];?>"><?php echo $data['number_of_option'];?></a></td>
      <td><?php echo $picoEdu->trueFalse($data['active'], 'Ya', 'Tidak');?> </td>
     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

<div class="button-area">
  <input type="submit" name="set_active" id="set_active" value="Aktifkan" class="btn btn-primary" />
  <input type="submit" name="set_inactive" id="set_inactive" value="Nonaktifkan" class="btn btn-warning" />
  </div>
</form>
<?php
}
else if(@$_GET['q'] != '')
{
?>
<div class="alert alert-warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="alert alert-warning">Data tidak ditemukan.</div>
<?php
}
?>
</div>

<?php
require_once __DIR__."/lib.inc/footer.php"; //NOSONAR
}
?>