<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($admin_login->admin_level != 1)
{
	include_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$admin_id = $admin_login->admin_id;
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
$cfg->module_title = "Soal Ujian";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
$time_create = $time_edit = $picoEdu->getLocalDateTime();
$member_create = $member_edit = $admin_login->admin_id;

if(@$_GET['option']=='delete')
{
	$question_id = kh_filter_input(INPUT_GET, 'question_id', FILTER_SANITIZE_NUMBER_UINT);
	$digest = kh_filter_input(INPUT_GET, 'digest', FILTER_SANITIZE_STRING_NEW_BASE64);
	$sql = "select * from `edu_question` where `question_id` = '$question_id' and `digest` = '$digest' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$id = $data['question_id'];
		$test_id = $data['test_id'];
		$sql = "DELETE FROM `edu_option` where `question_id` = '$id' ";
		$database->executeDelete($sql);
		$sql = "DELETE FROM `edu_question` where `question_id` = '$id' ";
		$database->executeDelete($sql);
		header("Location: ".basename($_SERVER['PHP_SELF'])."?test_id=$test_id");
	}
}

if(isset($_POST['savetext']) && @$_GET['option']=='add')
{
	// Format Plain
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
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
		$time_create = $picoEdu->getLocalDateTime();
		$time_edit = $picoEdu->getLocalDateTime();
		$member_create = $admin_id;
		$member_edit = $admin_id;
	
		$random = $data['random'];
		$order = $data['order'];
		$score_standar = $data['standard_score'];
		
		$xml_data = kh_filter_input(INPUT_POST, 'question_text', FILTER_DEFAULT);
		$clear_data = parseRawQuestion($xml_data);

		$base_dir = dirname(dirname(__FILE__))."/media.edu/school";
		if(!file_exists($base_dir))
		{
			mkdir($base_dir);
		}
		$base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id";
		if(!file_exists($base_dir))
		{
			mkdir($base_dir);
		}
		$base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test";
		if(!file_exists($base_dir))
		{
			mkdir($base_dir);
		}
		$base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
		if(!file_exists($base_dir))
		{
			mkdir($base_dir);
		}
		$base_src = "media.edu/school/$school_id/test/$test_id";
		$database->executeTransaction('start transaction');
		$oke = 1;
		
		foreach($clear_data as $question_no=>$question)
		{
			$object = parseQuestion($question);
			if(isset($object['question']) && isset($object['numbering']) && isset($object['option']))
			{
				$content = addslashes(nl2br(UTF8ToEntities(filter_html(addImages(@$object['question'], $base_dir, $base_src)))));
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
				$stmt1 = $database->executeInsert($sql1);
				if($stmt1->rowCount() == 0)
				{
					$oke = $oke * 0;
				}
				else
				{
					if(@is_array($object['option']) && count($object['option']) > 0)
					{
						foreach($object['option'] as $option_no=>$option)
						{
							$isi_option = addslashes(nl2br(UTF8ToEntities(filter_html(addImages($option['text'], $base_dir, $base_src)))));
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
			$database->executeTransaction('commit');
		}
		else
		{
			$database->executeTransaction('rollback');
		}
		header("Location: ".$_SERVER['REQUEST_URI']);
	}
}


if(isset($_POST['save']) && @$_GET['option']=='edit')
{
	$question_id = kh_filter_input(INPUT_POST, 'question_id', FILTER_SANITIZE_NUMBER_UINT);
	$numbering = kh_filter_input(INPUT_POST, 'numbering', FILTER_SANITIZE_STRING_NEW);
	$random = kh_filter_input(INPUT_POST, 'random', FILTER_SANITIZE_NUMBER_UINT);

	$sql = "SELECT `test_id` from `edu_question` where `question_id` = '$question_id' ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$dt = $stmt->fetch(PDO::FETCH_ASSOC);
		$test_id = $dt['test_id'];
		
		$direktori = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
		$prefiks = "media.edu/school/$school_id/test/$test_id";
	
		$question = kh_filter_input(INPUT_POST, 'question');
		$question = UTF8ToEntities($question);
		$question = addslashes(removeparagraphtag(extractImageData($question, $direktori, $prefiks))); 	
		
		$sql = "update `edu_question` set `content` = '$question' , `random` = '$random', `numbering` = '$numbering' where `question_id` = '$question_id' ";
		$stmt2 = $database->executeUpdate($sql);
		if($stmt2->rowCount())
		{
			$sql = "update `edu_question` set `time_edit` = '$time_edit', `member_edit` = '$member_edit' where `question_id` = '$question_id' ";
			$database->executeUpdate($sql);			
		}
		
		$sql = "select * from `edu_option` where `question_id` = '$question_id' ";
		$stmtx = $database->executeQuery($sql);
		if ($stmtx->rowCount() > 0) {
			$rowsx = $stmtx->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rowsx as $dt) {
				$id2 = $dt['option_id'];

				$option = kh_filter_input(INPUT_POST, 'option_' . $id2);
				$option = UTF8ToEntities($option);
				$option = addslashes(removeparagraphtag(extractImageData($option, $direktori, $prefiks)));

				$score = kh_filter_input(INPUT_POST, 'score_' . $id2, FILTER_SANITIZE_NUMBER_FLOAT);
				$sql = "update `edu_option` set `content` = '$option', `score` = '$score' where `question_id` = '$question_id' and `option_id` = '$id2' ";
				$stmt4 = $database->executeUpdate($sql);
				if ($stmt4->rowCount() > 0) {
					$sql = "update `edu_option` set `time_edit` = '$time_edit', `member_edit` = '$member_edit' 
					where `question_id` = '$question_id' and `option_id` = '$id2' ";
					$database->executeUpdate($sql);
				}
			}
		}
		if(@$_GET['ref'])
		{
			$ref = base64_decode($_GET['ref']);
			if (!empty($ref)) {
				header("Location: $ref");
			}
		}
	}
}


include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";



if(@$_GET['option']=='edit')
{
	include_once dirname(__FILE__)."/lib.inc/header.php";
	$question_id = kh_filter_input(INPUT_GET, 'question_id', FILTER_SANITIZE_STRING_NEW);
	$sql = "select * from `edu_question` where `question_id` = '$question_id' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$test_id = $data['test_id'];

		$sql = "SELECT `edu_test`.* ,
		(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id`) as `collection`
		from `edu_test` where `test_id` = '$test_id' ";
		$stmt3 = $database->executeQuery($sql);
		if ($stmt3->rowCount() > 0) {
			$data3 = $stmt3->fetch(PDO::FETCH_ASSOC);

			?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets; ?>lib.assets/theme/default/css/test.css" />
<script type="text/javascript" src="lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets; ?>';
var numbering = <?php echo json_encode($cfg->numbering); ?>;
var test_id = '<?php echo $data['test_id']; ?>';
var baseTestURLLength = <?php echo strlen("media.edu/school/$school_id/test/$test_id/"); ?>;	

function basename(path) {
return path.replace(/\\/g,'/').replace(/.*\//, '');
}
function dirname(path) {
return path.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
}
function getfileextension(filename){
return (/[.]/.exec(filename)) ? /[^.]+$/.exec(filename):'';
}
function removefileextension(filename){
return filename.replace(/\.[^/.]+$/, "");
}
var ascii_svg_server = 'lib.tools/asciisvg/svgimg.php';
var equation_preview_url = '../../../../../../cgi-bin/equgen.cgi?' ;
var equation_generator_url = '../../../../../../equgen.php?' ;
var equation_renderer_machine = (navigator.userAgent.toString().indexOf('Firefox') > -1)?'browser-png':'server-png';
var quran_server = '../quran';
$().ready(function() {
	$('textarea.htmleditor').tinymce({
		// Location of TinyMCE script
		script_url : 'lib.assets/script/tiny_mce/tiny_mce.js',

		// General options
		theme : "advanced",
        ascii_svg_server : ascii_svg_server,        
		equation_preview_url : equation_preview_url,        
		equation_generator_url : equation_generator_url, 
        equation_renderer_machine : equation_renderer_machine, 
		quran_server : quran_server, 
        ascii_svg_server : ascii_svg_server,
		plugins : "autolink,lists,style,table,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist,quran,latex,equation,chem,asciisvg,chart",
		theme_advanced_buttons1:"pasteword,pastetext,undo,redo,search,bold,italic,underline,strikethrough,formatselect,fontselect,fontsizeselect,justifyleft,justifycenter,justifyright,justifyfull,ltr,rtl,numlist,bullist,indent,outdent,blockquote",
		theme_advanced_buttons2:"visualaid,forecolor,backcolor,removeformat,anchor,link,unlink,image,media,quran,charmap,sup,sub,latex,equation,chem,asciisvg,chart,hr,table,row_props,cell_props,col_after,col_before,row_after,row_before,merge_cells,split_cells,delete_col,delete_row,delete_table,quran,arabiceditor,code,preview",
		theme_advanced_buttons3:"",
		theme_advanced_buttons4:"",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal:false,
		extended_valid_elements : "iframe[style|src|title|width|height|allowfullscreen|frameborder]",

		// Example content CSS (should be your site CSS)
		content_css : "lib.assets/theme/default/css/content.css",
		
		file_browser_callback:"fileBrowserCallBack",
		
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
			staffid : "612126"
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
var ajaxfilemanagerurl = "lib.tools/filemanager/?test_id="+test_id+"&editor=tiny_mce&type="+type+"&field_name="+field_name+'&dir=base/'+dirname(url);
switch (type){
case "image":break;
case "media":break;
case "flash":break;
case "file":break;
default:
return false;
}
tinyMCE.activeEditor.windowManager.open({url:ajaxfilemanagerurl,width:780,height:440,resizable:true,maximizable:true,inline:"yes",close_previous:"no"},{window:win,input:field_name});
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


<form id="form2" name="form2" method="post" action="">
<div class="test-info">
<table width="100%" border="0">
  <tr>
    <td width="160">Nama Ujian</td>
    <td><?php echo $data3['name']; ?></td>
  </tr>
  <tr>
    <td>Mata Pelajaran</td>
    <td><?php echo $data3['subject']; ?></td>
  </tr>
  <tr>
    <td>Jumlah Soal</td>
    <td><?php echo $data3['number_of_question']; ?> soal</td>
  </tr>
  <tr>
    <td>Jumlah Pilihan</td>
    <td><?php echo $data3['number_of_option']; ?> pilihan</td>
  </tr>
  <tr>
    <td>Koleksi Soal</td>
    <td><?php echo $data3['collection']; ?> soal <a href="ujian-soal.php?test_id=<?php echo $data3['test_id']; ?>">Lihat</a></td>
  </tr>
</table>
 </div>
<div class="question-area">
<fieldset>
<legend>Soal Ujian</legend>
<div class="question-prop">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="160">Tipe Pilihan</td>
    <td><select name="numbering" id="numbering" data-required="true" required="required">
      <option value="upper-alpha"<?php if ($data['numbering'] == 'upper-alpha')
					  echo ' selected="selected"'; ?>>A, B, C, D, ...</option>
      <option value="lower-alpha"<?php if ($data['numbering'] == 'lower-alpha')
					  echo ' selected="selected"'; ?>>a, b, c, d, ...</option>
      <option value="upper-roman"<?php if ($data['numbering'] == 'upper-roman')
					  echo ' selected="selected"'; ?>>I, II, III, IV, ...</option>
      <option value="lower-roman"<?php if ($data['numbering'] == 'lower-roman')
					  echo ' selected="selected"'; ?>>i, ii, iii, iv, ...</option>
      <option value="decimal"<?php if ($data['numbering'] == 'decimal')
					  echo ' selected="selected"'; ?>>1, 2, 3, 4, ...</option>
      <option value="decimal-leading-zero"<?php if ($data['numbering'] == 'decimal-leading-zero')
					  echo ' selected="selected"'; ?>>01, 02, 03, 04, ...</option>
    </select></td>
  </tr>
  <tr>
    <td>Pengacakan Pilihan</td>
    <td><label><input type="checkbox" name="random" id="random" value="1"<?php if ($data['random'])
						echo ' checked="checked"'; ?> /> Diacak</label></td>
  </tr>
</table>
</div>
<div class="question-editor">
<textarea spellcheck="false" class="htmleditor" name="question" id="question" style="width:100%;"><?php echo htmlspecialchars(($data['content'])); ?></textarea><input type="hidden" name="question_id" id="question_id" value="<?php echo $question_id; ?>" />
</div>
</fieldset>
</div>

<div class="option-area">
<fieldset>
<legend>Pilihan Jawaban</legend>

<?php
$numbering = $data['numbering'];
$sql = "select * from `edu_option` where `question_id` = '$question_id' ";
$i = 0;
$stmt2 = $database->executeQuery($sql);
if ($stmt2->rowCount() > 0) {
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $data2) {
?>
<div class="option-item" data-index="<?php echo $i; ?>">
<div class="option-score">Pilihan <span class="option-label"><?php echo $cfg->numbering[$numbering][$i]; ?></span> | Nilai <input type="number" min="0" max="<?php echo ($data3['standard_score']); ?>" class="input-text input-text-short" name="score_<?php echo $data2['option_id']; ?>" id="score_<?php echo $data2['option_id']; ?>" value="<?php echo $data2['score']; ?>" autocomplete="off" /> (Nilai Maksimum <?php echo ($data3['standard_score']); ?>)</div>
<div class="option-editor">
<textarea spellcheck="false" class="htmleditor" name="option_<?php echo $data2['option_id']; ?>" id="option_<?php echo $data2['option_id']; ?>" style="width:100%;"><?php echo htmlspecialchars(($data2['content'])); ?></textarea>
</div>
</div>
<?php
		$i++;
}
}
?>
</fieldset>
</div>


<div class="button-area">
<input type="submit" name="save" id="save" class="com-button" value="Simpan" />
<input type="button" name="showall" id="showall" class="com-button" value="Tampilkan Semua Soal" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']); ?>?test_id=<?php echo $test_id; ?>'" />
</div>

</form>
<?php
} else {
?>
<div class="warning">Ujian tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']); ?>">Klik di sini untuk kembali.</a></div>
<?php
}
}

include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else if(isset($_GET['test_id']))
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_test`.* ,
(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id`) as `collection`
from `edu_test` where `test_id` = '$test_id' 
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet" href="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css">
<form name="form1" method="post" action="" enctype="multipart/form-data">
<div class="test-info">
<table width="100%" border="0">
  <tr>
    <td width="160">Nama Ujian</td>
    <td><?php echo $data['name'];?></td>
  </tr>
  <tr>
    <td>Mata Pelajaran</td>
    <td><?php echo $data['subject'];?></td>
  </tr>
  <tr>
    <td>Jumlah Soal</td>
    <td><?php echo $data['number_of_question'];?> soal</td>
  </tr>
  <tr>
    <td>Jumlah Pilihan</td>
    <td><?php echo $data['number_of_option'];?> pilihan</td>
  </tr>
  <tr>
    <td>Koleksi Soal</td>
    <td><span id="total_collection"><?php echo $data['collection'];?></span> soal <a href="ujian-soal.php?test_id=<?php echo ($data['test_id']);?>">Lihat</a></td>
  </tr>
</table>
 </div>
<?php

$number_of_option = $data['number_of_option'];
$caption_option = array();
for($i=0;$i<$number_of_option;$i++)
{
	$caption_option[$i] = chr(65+$i);
}

if(@$_GET['option'] == 'analys')
{
$sql = "select * from `edu_question` where `test_id` = '$test_id' order by `order` asc ";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
?>
<table width="100%" border="1" cellspacing="0" cellpadding="0" class="row-table">
<thead>
  <tr>
    <td width="20">No</td>
    <td width="30">Lihat</td>
    <td>Potongan Soal</td>
    <td align="center" width="50">Jawaban</td>
    <?php
	for($i=0;$i<$number_of_option;$i++)
	{
	?>
    <td align="center" width="20"><?php echo $caption_option[$i];?></td>
    <?php
	}
	?>
    <td align="right" width="50">Menjawab</td>
    <td align="right" width="40">Benar</td>
    <td align="right" width="40">Salah</td>
    <td align="right" width="50">%Benar</td>
  </tr>
</thead>

<tbody>
<?php
$no = 0;
$total_menjawab = 0;
$total_benar = 0;
$total_salah = 0;
$total_persen = 0;
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $data)
{
	$no++;
	$question_id = $data['question_id'];
	if(stripos($data['content'], "<p") === false)
	{
		$data['content'] = "<p>".$data['content']."</p>";
	}
	$obj = parseHtmlData('<html><body>'.($data['content']).'</body></html>');
	$arrparno = array();
	$arrparlen = array();
	$cntmax = ""; // do not remove
	$content = ""; // do not remove
	$i = 0;
	$minlen = 10;
	
	if(isset($obj->p) && count($obj->p)>0)
	{
		$max = 0;
		foreach($obj->p as $parno=>$par)
		{
			$arrparlen[$i] = strlen(trim(strip_tags($par), " \r\n\t&nbsp; "));
			if($arrparlen[$i]>$max)
			{
				$max = $arrparlen[$i];
				$cntmax = $par;
			}
			if($arrparlen[$i] >= $minlen)
			{
				$content = $par;
				break;
			}
		}
		if(!$content)
		{
			
			$content = $cntmax;
		}
	}
	
	$sql2 = "SELECT `edu_option`.*,
	(select count(distinct `edu_answer`.`answer_id`) 
		from `edu_answer` 
		where `edu_answer`.`answer` like concat('%,',`edu_option`.`option_id`,']%')
		group by `edu_answer`.`test_id`
		limit 0,1
		) as `pilih`
	from `edu_option`
	where `edu_option`.`question_id` = '$question_id' ";
	$answer = '';
	$option = array();
	$j = 0;
	$score = 0;
	$menjawab = 0;
	$stmt2 = $database->executeQuery($sql2);
	if ($stmt2->rowCount() > 0) {
		$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows2 as $data2) {
			$option[$j] = $data2['pilih'];
			if ($data2['score'] > $score) {
				$score = $data2['score'];
				$answer = $j;
			}
			$menjawab += $data2['pilih'];
			$j++;
		}
	}
	
?>
  <tr>
    <td align="right"><?php echo $no;?></td>
    <td><a href="#" class="show-question" data-number="<?php echo $no;?>" data-question-id="<?php echo $question_id;?>">Lihat</a></td>
    <td><?php echo substr($content, 0, 70);?>...</td>
    <td align="center"><?php echo @$caption_option[$answer];?></td>
    <?php
	for($i=0;$i<$number_of_option;$i++)
	{
	?>
    <td align="right"><?php echo @$option[$i];?></td>
    <?php
	}
	?>
    <td align="right"><?php echo $menjawab;?></td>
    <td align="right"><?php echo @$option[$answer]+0;?></td>
    <td align="right"><?php echo $menjawab-@$option[$answer];?></td>
    <td align="right"><?php if($menjawab != 0) { echo $picoEdu->numberFormatTrans(100*(@$option[$answer]+0)/$menjawab, true);} ?></td>
  </tr>
<?php
	$total_menjawab += $menjawab;
	$total_benar += @$option[$answer];
	$total_salah += $menjawab-@$option[$answer];
}
if($total_menjawab != 0)
{
	$total_persen = 100*$total_benar/$total_menjawab;
}
else
{
	$total_persen = 0;
}
?>
</tbody>

<tfoot>
  <tr>
    <td colspan="<?php echo $number_of_option+4;?>">Total</td>
    <td align="right"><?php echo $total_menjawab;?></td>
    <td align="right"><?php echo $total_benar;?></td>
    <td align="right"><?php echo $total_salah;?></td>
    <td align="right"><?php echo $picoEdu->numberFormatTrans($total_persen, true);?></td>
  </tr>
</tfoot>
</table>

<div class="button-area">
	<input type="button" class="com-button" name="export" id="export" value="Ekspor" onclick="window.open('ujian-analisa.php?test_id=<?php echo $test_id;?>');" />
</div>

<div class="dialogs" style="display:none;">
	<div class="dialog-question" title="Soal Ujian">
    	<div class="dialog-question-inner"></div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(e) {
    $(document).on('click', '.show-question', function(e){
		var question_id = $(this).attr('data-question-id');
		var number = $(this).attr('data-number');
		$('.dialog-question-inner').html('');
		$('.dialog-question').dialog({
			modal:true,
			title:'Soal Ujian',
			width:720,
			height:400
		});
		$.get('ajax-load-question.php', {question_id:question_id, number:number}, function(answer){
			$('.dialog-question-inner').html(answer);
		});
		e.preventDefault();
	});
});
</script>

<?php
}
}
else
{
?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test.css" />
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.min.js"></script>
<link rel="stylesheet" href="<?php echo $cfg->base_assets;?>lib.assets/script/jquery-ui/jquery-ui.min.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery.ui.touch-punch.js"></script>
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
	$("#sortable").sortable({
		placeholder: "ui-state-highlight",
		forcePlaceholderSize: true,
		revert: true,
		change:function(event, ui)
		{
		},
		stop: function(event, ui)
		{
			var array_question = [];
			$("#sortable > li").each(function(index, element) {
			array_question.push($(this).attr('data-question-id'));
			});
			$.post('ajax-sort-question.php', {array_question:array_question.join(','), sort:'yes'}, function(answer){
			});
		}
	});
	$("#sortable").disableSelection();
}
window.onload=function(){
	$('.deletequestion').click(function(){
		return confirm('Apakah Anda akan menghapus soal ini beserta dengan seluruh pilihannya?');
	});

}
</script>
<ol id="sortable" class="test-question">
<?php
$sql = "select * 
from `edu_question` where `test_id` = '$test_id' 
order by `order` asc, `question_id` asc
";
$stmt = $database->executeQuery($sql);
if ($stmt->rowCount() > 0) {
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $data){
?>
<li data-question-id="<?php echo $data['question_id']; ?>">
<div class="question-edit-ctrl">
<a class="button-gradient" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=edit&question_id=<?php echo $data['question_id']; ?>&ref=<?php echo base64_encode($_SERVER['REQUEST_URI']); ?>">Ubah Soal</a> 
<a class="button-gradient deletequestion" href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=delete&question_id=<?php echo $data['question_id']; ?>&digest=<?php echo $data['digest']; ?>">Hapus Soal</a> </div>
<div class="question">
<?php
echo $data['content'];
?>
<div class="option">
<ol class="listoption" style="list-style-type:<?php echo $data['numbering']; ?>">
<?php
$question_id = $data['question_id'];
$sql2 = "select * from `edu_option` where `question_id` = '$question_id' ";
$stmt2 = $database->executeQuery($sql2);
if ($stmt2->rowCount() > 0) 
{
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $data2) {
?>
<li>
<span class="option-circle<?php if ($data2['score'])
echo ' option-circle-selected'; ?>"><?php
echo $data2['score'] * 1;
?></span>
<div class="list-option-item">
<div class="option-content">
<?php
echo ($data2['content']);
?>
</div>
</div>
</li>
<?php
}
}
?>
</ol>
</div>
</div>


</li>
<?php
}
}
?>
</ol>
<div class="button-area">
<input type="button" name="urutkan_soal" id="urutkan_soal" class="com-button" value="Urutkan Soal" onclick="activateSortOrder()" />
<input type="button" name="export" id="export" class="com-button" value="Ekspor Soal" onclick="window.location='ujian-ekspor.php?test_id=<?php echo $test_id;?>'" />
<input type="button" name="analys" id="analys" class="com-button" value="Analisa Butir Soal" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=analys&test_id=<?php echo $test_id;?>'" />
<input type="button" name="show" id="show" class="com-button" value="Tampilkan Informasi Ujian" onclick="window.location='ujian-daftar.php?option=detail&test_id=<?php echo $test_id;?>'" />
<input type="button" name="edit" id="edit" class="com-button" value="Ubah Informasi Ujian" onclick="window.location='ujian-daftar.php?option=edit&test_id=<?php echo $test_id;?>'" />
</div>
<?php
}
?>
</form>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}

} else {
	
		include_once dirname(__FILE__) . "/lib.inc/header.php";
		$school_id = kh_filter_input(INPUT_GET, 'school_id', FILTER_SANITIZE_STRING_NEW);
		$class_id = kh_filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_STRING_NEW);
		?>
<style type="text/css">
.menu-control{
	margin:0;
	padding:2px 0;
	position:absolute;
	z-index:100;
	left:30px;
	top:100px;
	background-color:#FFFFFF;
	border:1px solid #DDDDDD;
	box-shadow:0 0 3px #E5E5E5;
	display:none;
}
.menu-control::before{
	content:"";
	width:10px;
	height:0px;
	border:10px solid transparent;
	border-right:10px solid #DDDDDD;
	position:absolute;
	margin-left:-30px;
	margin-top:30px;
}
.menu-control li{
	list-style-type:none;
	margin:0;
	padding:0 2px;
}
.menu-control > li:first-child::before{
	content:"";
	width:9px;
	height:0px;
	border:9px solid transparent;
	border-right:9px solid #FFFFFF;
	position:absolute;
	margin-left:-28px;
	margin-top:31px;
}
.menu-control li a{
	background-color:#FEFEFE;
	display:block;
	padding:5px 16px;
	border-bottom:1px solid #EEEEEE;
}
.menu-control li a:hover{
	background-color:#428AB7;
	color:#FFFFFF;
}
.menu-control li:last-child a{
	border-bottom:none;
}
</style>
<script type="text/javascript">


window.onload = function()
{
	$(document).on('change', '#searchform select', function(e){
		$(this).closest('form').submit();
	});
	$(document).on('click', '.show-controls', function(e){
		var obj = $(this);
		if(obj.hasClass('menu-show'))
		{
			$('.show-controls').each(function(index, element) {
				$(this).removeClass('menu-show');
			});
			$('.menu-control').css({display:'none'});
		}
		else
		{
			$('.show-controls').each(function(index, element) {
				$(this).removeClass('menu-show');
			});
			var left = obj.offset().left + 40;
			var top = obj.offset().top - 34;
			var id = obj.attr('data-test-id');
			obj.addClass('menu-show');
			$('.menu-control').empty().append(buildMenu(id)).css({left:left, top:top, display:'block'});
		}
		e.preventDefault();
	});
}
function buildMenu(id)
{
	var html = 
	'<li><a href="ujian-soal.php?test_id='+id+'">Tampilkan Soal Ujian</a></li>\r\n'+
	'<li><a href="ujian-ekspor.php?test_id='+id+'">Ekspor Soal Ujian</a></li>\r\n'+
	'<li><a href="ujian-soal.php?option=analys&test_id='+id+'">Analisa Soal Ujian</a></li>\r\n'+
	'<li><a href="ujian-laporan.php?option=detail&test_id='+id+'">Laporan Hasil Ujian</a></li>\r\n'+
	'<li><a href="ujian-daftar.php?option=edit&test_id='+id+'">Ubah Informasi Ujian</a></li>\r\n'
	;
	return html;
}





</script>

<ul class="menu-control">
</ul>

<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
  <span class="search-label">Sekolah</span>
  <select class="input-select" name="school_id" id="school_id">
    <option value="">- Pilih Sekolah -</option>
    <?php
			$sql2 = "select * from `edu_school` where 1 order by `school_id` desc ";
			$stmt2 = $database->executeQuery($sql);
			if ($stmt2->rowCount() > 0) {
				$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rows2 as $data2) {
					?>
        <option value="<?php echo $data2['school_id']; ?>"<?php if ($school_id == $data2['school_id'])
						 echo ' selected="selected"'; ?>><?php echo $data2['name']; ?></option>
        <?php
				}
			}
			?>
    </select>
    <?php
			if (@$school_id != 0) {
				?>
    <span class="search-label">Kelas</span> 
    <select class="input-select" name="class_id" id="class_id">
    <option value="">- Pilih Kelas -</option>
    <?php
					$sql = "select * from `edu_class` where `school_id` = '$school_id' ";
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
    <?php
			}
			?>
    <span class="search-label">Ujian</span>
    <input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q'], " 	
    ")))); ?>" />
    <input type="submit" name="search" id="search" value="Cari" class="com-button" />
</form>
</div>
<div class="search-result">
<?php
				$sql_filter = "";
				$pagination->array_get = array();
				if ($pagination->query) {
					$pagination->array_get[] = 'q';
					$sql_filter .= " and (`edu_test`.`name` like '%" . addslashes($pagination->query) . "%' )";
				}

				if ($school_id != 0) {
					$pagination->array_get[] = 'school_id';
					$sql_filter .= " and (`edu_test`.`school_id` = '$school_id' )";
				}
				if ($class_id != '') {
					$sql_filter .= " and concat(',',`edu_test`.`class`,',') like '%,$class_id,%' ";
					$pagination->array_get[] = 'class_id';
				}

				$nt = '';


				$sql = "SELECT `edu_test`.* $nt,
				(select `edu_school`.`name` from `edu_school` where `edu_school`.`school_id` = `edu_test`.`school_id` limit 0,1) as `school_name`,
				(select `edu_teacher`.`name` from `edu_teacher` where `edu_teacher`.`teacher_id` = `edu_test`.`teacher_id`) as `teacher`,
				(select count(distinct `edu_question`.`question_id`) from `edu_question` where `edu_question`.`test_id` = `edu_test`.`test_id` group by `edu_question`.`test_id`)*1 as `number_of_question`
				from `edu_test`
				where 1 $sql_filter
				order by `edu_test`.`test_id` desc
				";

				$sql_test = "SELECT `edu_test`.`test_id`
				from `edu_test`
				where 1 $sql_filter
				";

				$stmt = $database->executeQuery($sql_test);
				$pagination->total_record = $stmt->rowCount();
				$stmt = $database->executeQuery($sql . $pagination->limit_sql);

				$pagination->total_record_with_limit = $stmt->rowCount();
				if ($pagination->total_record_with_limit > 0) {
					$pagination->start = $pagination->offset + 1;
					$pagination->end = $pagination->offset + $pagination->total_record_with_limit;

					$pagination->result = $picoEdu->createPagination(
						basename($_SERVER['PHP_SELF']), $pagination->total_record, $pagination->limit, $pagination->num_page,
						$pagination->offset, $pagination->array_get,
						true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next
					);
					$pagination->str_result = "";
					foreach ($pagination->result as $i => $obj) {
						$cls = ($obj->sel) ? " class=\"pagination-selected\"" : "";
						$pagination->str_result .= "<a href=\"" . $obj->ref . "\"$cls>" . $obj->text . "</a> ";
					}
					?>
<?php
						$array_class = $picoEdu->getArrayClass($school_id);
						?>
<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:800px)
{
}
@media screen and (max-width:599px)
{
}
@media screen and (max-width:399px)
{
}
</style>

<div class="search-pagination search-pagination-top">
<div class="search-pagination-control"><?php echo $pagination->str_result; ?></div>
<div class="search-pagination-label"><?php echo $pagination->start; ?>-<?php echo $pagination->end; ?>/<?php echo $pagination->total_record; ?></div>
</div>

  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table hide-some-cell">
  <thead>
    <tr>
      <td width="16"><img src="lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Detail" border="0" /></td>
      <td width="25">No</td>
      <td>Sekolah</td>
      <td>Nama Ujian</td>
      <td>Pelajaran</td>
      <td>Kelas</td>
      <td>Soal</td>
      </tr>
    </thead>
    <tbody>
    <?php
					$no = $pagination->offset;
					$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $data) {
						$no++;
						?>
    <tr<?php $rowclass = "";
							if (@$data['default'] == 1)
								$rowclass .= " data-default";
							if (isset($data['active'])) {
								if (@$data['active'] == 1)
									$rowclass .= " data-active";
								if (@$data['active'] == 0)
									$rowclass .= " data-inactive";
							}
							$rowclass = trim($rowclass);
							if (strlen($rowclass)) {
								echo " class=\"$rowclass\"";
							} ?>>
      <td><a class="show-controls" data-test-id="<?php echo $data['test_id']; ?>" href="ujian-soal.php?option=detail&test_id=<?php echo $data['test_id']; ?>"><img src="lib.tools/images/trans.gif" class="icon-16 icon-browse-16" alt="Detail" border="0" /></a></td>
      <td align="right"><?php echo $no; ?></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php echo ($data['school_name']); ?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php echo $data['name']; ?></a></td>
      <td><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?option=detail&test_id=<?php echo $data['test_id']; ?>"><?php echo $data['subject']; ?></a></td>
      <td><?php $class = $picoEdu->textClass($array_class, $data['class']);
						  $class_sort = $picoEdu->textClass($array_class, $data['class'], 2); ?><a href="#" class="class-list-control" data-class="<?php echo htmlspecialchars($class); ?>"><?php echo $class_sort; ?></a></td>
      <td><?php if ($data['number_of_question']) { ?><a href="<?php echo basename($_SERVER['PHP_SELF']); ?>?test_id=<?php echo $data['test_id']; ?>"><?php echo $data['number_of_question']; ?> soal</a><?php } else {
							  echo '-';
						  } ?></td>
      </tr>
    <?php
					}
					?>
    </tbody>
  </table>

<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result; ?></div>
<div class="search-pagination-label"><?php echo $pagination->start; ?>-<?php echo $pagination->end; ?>/<?php echo $pagination->total_record; ?></div>
</div>




</form>
<?php
				} else if (@$_GET['q']) {
					?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
				} else {
					?>
<div class="warning">Data tidak ditemukan. </div>
<?php
				}
				?>
</div>

<?php
	
include_once dirname(__FILE__)."/lib.inc/footer.php";
}

?>