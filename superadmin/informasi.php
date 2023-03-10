<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}
$pageTitle = "Informasi";

$pagination = new \Pico\PicoPagination();

if(isset($_POST['set_active']) && isset($_POST['info_id']))
{
	$infos = @$_POST['info_id'];
	if(isset($infos) && is_array($infos))
	{
		foreach($infos as $key=>$val)
		{
			$info_id = addslashes($val);
			$sql = "UPDATE `edu_info` SET `active` = true WHERE `info_id` = '$info_id'  ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['set_inactive']) && isset($_POST['info_id']))
{
	$infos = @$_POST['info_id'];
	if(isset($infos) && is_array($infos))
	{
		foreach($infos as $key=>$val)
		{
			$info_id = addslashes($val);
			$sql = "UPDATE `edu_info` SET `active` = false WHERE `info_id` = '$info_id'  ";
			$database->executeUpdate($sql, true);
		}
	}
}
if(isset($_POST['delete']) && isset($_POST['info_id']))
{
	$infos = @$_POST['info_id'];
	if(isset($infos) && is_array($infos))
	{
		foreach($infos as $key=>$val)
		{
			$info_id = addslashes($val);
			$sql = "UPDATE `edu_info` SET `school_id` = '' WHERE `info_id` = '$info_id'  ";
			$database->executeUpdate($sql, true);
		}
	}
}

if(isset($_POST['publish']) || isset($_POST['draff']))
{
	$option = kh_filter_input(INPUT_POST, "option", FILTER_SANITIZE_SPECIAL_CHARS);
	$name = trim(kh_filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS));
	if(empty($name))
	{
		$name = '(Tanpa Judul)';
	}
	
	$active = 0;
	$time = $database->getLocalDateTime();
	$ip = $_SERVER['REMOTE_ADDR'];
	
	if(isset($_POST['publish']))
	{
		$active = 1;
	}
	if($option == 'add')
	{
		$info_id = $database->generateNewId();
		$sql = "INSERT INTO `edu_info`
		(`info_id`, `name`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES	
		('$info_id', '$name', '$time', '$time', '$admin_id', '$admin_id', '$ip', '$ip', '$active')
		";
		$stmt = $database->executeInsert($sql, true);
		if ($stmt->rowCount() > 0) {

			$info_dir = dirname(dirname(__FILE__)) . "/media.edu/info/$info_id";
			$base_src = "media.edu/info/$info_id";

			$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/info/$info_id";
			$dirBase = dirname(dirname(__FILE__));
			$permission = 0755;
			$fileSync->prepareDirectory($info_dir, $dirBase, $permission, true);

			if (!file_exists($info_dir = dirname(dirname(__FILE__)) . "/media.edu")) {
				mkdir(dirname(dirname(__FILE__)) . "/media.edu", 0755);
			}
			if (!file_exists($info_dir = dirname(dirname(__FILE__)) . "/media.edu/info")) {
				mkdir(dirname(dirname(__FILE__)) . "/media.edu/info", 0755);
			}
			if (!file_exists($info_dir = dirname(dirname(__FILE__)) . "/media.edu/info/$info_id")) {
				mkdir(dirname(dirname(__FILE__)) . "/media.edu/info/$info_id", 0755);
			}

			$content = kh_filter_input(INPUT_POST, "content");
			$content = \Pico\PicoDOM::extractImageData($content, $info_dir, $base_src, $fileSync);
			$content = addslashes(utf8ToEntities($content));

			$sql = "UPDATE `edu_info` SET
			`content` = '$content'
			WHERE `info_id` = '$info_id'
			";
			$database->executeUpdate($sql, true);
		}
		
		header("Location: ".$picoEdu->gateBaseSelfName()."?option=edit&info_id=$info_id");
	}
	else if($option == 'edit')
	{
		$info_id = kh_filter_input(INPUT_POST, "info_id");
		
		$info_dir = dirname(dirname(__FILE__)) . "/media.edu/info/$info_id";
		$base_src = "media.edu/info/$info_id";

		$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/info/$info_id";
		$dirBase = dirname(dirname(__FILE__));
		$permission = 0755;
		$fileSync->prepareDirectory($info_dir, $dirBase, $permission, true);
		
		$content = kh_filter_input(INPUT_POST, "content");
		$content = \Pico\PicoDOM::extractImageData($content, $info_dir, $base_src, $fileSync);
		$content = addslashes(utf8ToEntities($content));

		$sql = "UPDATE `edu_info` SET
		`name` = '$name', `content` = '$content', 
		`time_edit` = '$time', `admin_edit` = '$admin_id', `ip_edit` =  '$ip', `active` = '$active'
		WHERE `info_id` = '$info_id'
		";
		$database->executeUpdate($sql, true);
		header("Location: ".$picoEdu->gateBaseSelfName()."?option=detail&info_id=$info_id");
	}
}



if(@$_GET['option'] == 'add')
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
</script>
<script type="text/javascript" src="../lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
function addslashes(input){
var searchStr = "\'";
var replaceStr = "\\'";
var re = new RegExp(searchStr , "g");
var output = input.replace(re, replaceStr);
return output;
}
function basename(path){
return path.replace(/\\/g,'/').replace(/.*\//,'');
}
function dirname(path){
return path.replace(/\\/g,'/').replace(/\/[^\/]*$/,'');
}
function getfileExtension(filename){
return (/[.]/.exec(filename))?/[^.]+$/.exec(filename):'';
}
function removefileextension(filename){
return filename.replace(/\.[^/.]+$/,'');
}

var ascii_svg_server = '../lib.tools/asciisvg/svgimg.php';
var equation_preview_url = '../../../../../../cgi-bin/equgen.cgi?' ;
var equation_generator_url = '../../../../../../equgen.php?' ;
var equation_renderer_machine = (navigator.userAgent.toString().indexOf('Firefox') > -1)?'mathml-png':'mathjax-svg';
var quran_server = '../quran';
$().ready(function() {
	if($('textarea').length)
	{
	$('textarea#content').tinymce({
		// Location of TinyMCE script
		script_url : '../lib.assets/script/tiny_mce/tiny_mce.js',
		// General options
		theme : "advanced",
        ascii_svg_server : ascii_svg_server,        
		equation_preview_url : equation_preview_url,        
		equation_generator_url : equation_generator_url, 
        equation_renderer_machine : equation_renderer_machine, 
		quran_server : quran_server, 
		plugins : "autolink,lists,style,table,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist,quran,latex,equation,chem,asciisvg,chart,chart,draw",
		theme_advanced_buttons1:"pasteword,pastetext,undo,redo,search,bold,italic,underline,strikethrough,formatselect,fontselect,fontsizeselect,justifyleft,justifycenter,justifyright,justifyfull,ltr,rtl,numlist,bullist,indent,outdent,blockquote",
		theme_advanced_buttons2:"visualaid,forecolor,backcolor,removeformat,anchor,link,unlink,image,media,charmap,quran,sup,sub,latex,equation,chem,asciisvg,chart,draw,hr,table,row_props,cell_props,col_after,col_before,row_after,row_before,merge_cells,split_cells,delete_col,delete_row,delete_table,quran,arabiceditor,code,preview",
		theme_advanced_buttons3:"",
		theme_advanced_buttons4:"",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal:false,
		extended_valid_elements : "iframe[style|src|title|width|height|allowfullscreen|frameborder]",

		// Example content CSS (should be your site CSS)
		content_css : "../lib.assets/theme/default/css/content.css",
		
		file_browser_callback:"fileBrowserCallBack",
		
		apply_source_formatting:true,
		accessibility_warnings:false,

		// Replace values for the template plugin
		template_replace_values : {
			username : "Kamshory",
			staffid : "612126"
		}
	});
	}
	$(document).on('click', '#select-class', function(e){
		selectClass();
		e.preventDefault();
	}); 
});
	
	
function fileBrowserCallBack(field_name, url, type, win)
{
	if(editState == 'add')
	{
		if(confirm('Anda harus menyimpan informasi ini terlebih dahulu sebelum melanjutkan. Apakah Anda akan menyimpan artikel ini sekarang?'))
		{
			$('#infoform').append('<input type="hidden" name="draff" value="Draff">').submit();
		}
	}
	else
	{
		var article_id = $('#articleform').find('#article_id').val();
		if(url.indexOf('data:') != -1)
		{
			url = '';
		}
		if(url == '')
		{
			url = defaultdir;
		}
		url = url.substr(21);
		
		var ajaxFilemanagerURL = "lib.tools/filemanager/?section=info&info_id="+info_id+"&editor=tiny_mce&type="+type+"&field_name="+field_name+'&dir=base/'+dirname(url);
		switch (type){
			case "image":break;
			case "media":break;
			case "flash":break;
			case "file":break;
			default:
			return false;
		}
		tinyMCE.activeEditor.windowManager.open({url:ajaxFilemanagerURL,width:800,height:480,resizable:true,maximizable:true,inline:"yes",close_previous:"no"},{window:win,input:field_name});
	}
}
</script>
<style type="text/css">
input.input-text-full[type="text"]{
	font-size:14px;
	padding:6px 10px;
	border-collapse:#CCCCCC;
}
</style>
<script type="text/javascript">
var editState = 'add';
var defaultdir = 'lib.content/media/info/';
</script>
<form id="infoform" method="post" enctype="multipart/form-data" action="">
<div class="input-block">
<input type="text" id="name" name="name" class="form-control input-text input-text-full input-text-name" placeholder="Judul Informasi" autocomplete="off" required="required" />
</div>
<div class="input-block">
<textarea id="content" name="content" style="width:100%; height:300px; box-sizing:border-box;"></textarea>
<input type="hidden" name="class" id="classlist" value="" />
</div>
<div class="button-area">
<input class="btn btn-success" type="submit" id="publish" name="publish" value="Publikasikan" />
<input class="btn btn-success" type="submit" id="draff" name="draff" value="Simpan Konsep" />
<input type="hidden" name="option" id="option" value="add" />
<input class="btn btn-secondary" type="button" id="cancel" name="cancel" value="Batalkan" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" />
</div>
</form>
<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(@$_GET['option'] == 'edit' && isset($_GET['info_id']))
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
$info_id = kh_filter_input(INPUT_GET, "info_id", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT * FROM `edu_info` WHERE `info_id` = '$info_id'";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(\PDO::FETCH_ASSOC);
?>
<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
</script>
<script type="text/javascript" src="../lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
function addslashes(input){
var searchStr = "\'";
var replaceStr = "\\'";
var re = new RegExp(searchStr , "g");
var output = input.replace(re, replaceStr);
return output;
}
function basename(path){
return path.replace(/\\/g,'/').replace(/.*\//,'');
}
function dirname(path){
return path.replace(/\\/g,'/').replace(/\/[^\/]*$/,'');
}
function getfileExtension(filename){
return (/[.]/.exec(filename))?/[^.]+$/.exec(filename):'';
}
function removefileextension(filename){
return filename.replace(/\.[^/.]+$/,'');
}

var ascii_svg_server = '../lib.tools/asciisvg/svgimg.php';
var equation_preview_url = '../../../../../../cgi-bin/equgen.cgi?' ;
var equation_generator_url = '../../../../../../equgen.php?' ;
var equation_renderer_machine = (navigator.userAgent.toString().indexOf('Firefox') > -1)?'mathml-png':'mathjax-svg';
var quran_server = '../quran';
$().ready(function() {
	if($('textarea').length)
	{
	$('textarea#content').tinymce({
		// Location of TinyMCE script
		script_url : '../lib.assets/script/tiny_mce/tiny_mce.js',
		// General options
		theme : "advanced",
        ascii_svg_server : ascii_svg_server,        
		equation_preview_url : equation_preview_url,        
		equation_generator_url : equation_generator_url, 
        equation_renderer_machine : equation_renderer_machine, 
		quran_server : quran_server, 
		plugins : "autolink,lists,style,table,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist,quran,latex,equation,chem,asciisvg,chart,draw",
		theme_advanced_buttons1:"pasteword,pastetext,undo,redo,search,bold,italic,underline,strikethrough,formatselect,fontselect,fontsizeselect,justifyleft,justifycenter,justifyright,justifyfull,ltr,rtl,numlist,bullist,indent,outdent,blockquote",
		theme_advanced_buttons2:"visualaid,forecolor,backcolor,removeformat,anchor,link,unlink,image,media,charmap,quran,sup,sub,latex,equation,chem,asciisvg,chart,draw,hr,table,row_props,cell_props,col_after,col_before,row_after,row_before,merge_cells,split_cells,delete_col,delete_row,delete_table,quran,arabiceditor,code,preview",
		theme_advanced_buttons3:"",
		theme_advanced_buttons4:"",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal:false,
		extended_valid_elements : "iframe[style|src|title|width|height|allowfullscreen|frameborder]",

		// Example content CSS (should be your site CSS)
		content_css : "../lib.assets/theme/default/css/content.css",
		
		file_browser_callback:"fileBrowserCallBack",
		
		apply_source_formatting:true,
		accessibility_warnings:false,

		// Replace values for the template plugin
		template_replace_values : {
			username : "Kamshory",
			staffid : "612126"
		}
	});
	}
	$(document).on('click', '#select-class', function(e){
		selectClass();
		e.preventDefault();
	}); 
});
	
	
function fileBrowserCallBack(field_name, url, type, win)
{
	if(editState == 'add')
	{
		if(confirm('Anda harus menyimpan informasi ini terlebih dahulu sebelum melanjutkan. Apakah Anda akan menyimpan artikel ini sekarang?'))
		{
			$('#infoform').append('<input type="hidden" name="draff" value="Draff">').submit();
		}
	}
	else
	{
		var article_id = $('#articleform').find('#article_id').val();
		if(url.indexOf('data:') != -1)
		{
			url = '';
		}
		if(url == '')
		{
			url = defaultdir;
		}
		url = url.substr(21);
		
		var ajaxFilemanagerURL = "lib.tools/filemanager/?section=info&info_id="+info_id+"&editor=tiny_mce&type="+type+"&field_name="+field_name+'&dir=base/'+dirname(url);
		switch (type){
			case "image":break;
			case "media":break;
			case "flash":break;
			case "file":break;
			default:
			return false;
		}
		tinyMCE.activeEditor.windowManager.open({url:ajaxFilemanagerURL,width:800,height:480,resizable:true,maximizable:true,inline:"yes",close_previous:"no"},{window:win,input:field_name});
	}
}
</script>
<style type="text/css">
input.input-text-full[type="text"]{
	font-size:14px;
	padding:6px 10px;
	border-collapse:#CCCCCC;
}
</style>
<script type="text/javascript">
var editState = 'edit';
var defaultdir = 'lib.content/media/info/';
var info_id = '<?php echo $info_id;?>';
</script>
<form id="infoform" method="post" enctype="multipart/form-data" action="">
<div class="input-block">
<input type="text" id="name" name="name" class="form-control input-text input-text-full input-text-name" value="<?php echo $data['name'];?>" placeholder="Judul Informasi" autocomplete="off" required="required" />
</div>
<div class="input-block">
<textarea id="content" name="content" style="width:100%; height:300px; box-sizing:border-box;"><?php echo htmlspecialchars($data['content']);?></textarea>
<input type="hidden" name="class" id="classlist" value="<?php echo $data['class'];?>" />
</div>
<div class="button-area">
<input class="btn btn-success" type="submit" id="publish" name="publish" value="Publikasikan" />
<input class="btn btn-success" type="submit" id="draff" name="draff" value="Simpan Konsep" />
<input type="hidden" name="option" id="option" value="edit" />
<input type="hidden" name="info_id" id="info_id" value="<?php echo $info_id;?>" />
<input class="btn btn-secondary" type="button" id="cancel" name="cancel" value="Batalkan" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>'" />
</div>
</form>
<?php
}
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else if(isset($_GET['info_id']))
{
	require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$info_id = kh_filter_input(INPUT_GET, "info_id", FILTER_SANITIZE_STRING_NEW);
	$sql_filter_info = " AND `edu_info`.`info_id` = '$info_id' ";

	$sql = "SELECT `edu_info`.*, `member`.`name` AS `creator`
	FROM `edu_info` 
	LEFT JOIN (`member`) ON (`member`.`member_id` = `edu_info`.`admin_create`) 
	WHERE (1=1) $sql_filter_info ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
		?>
		<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
		<script type="text/javascript">
		$(document).ready(function(e) {
			$(document).on('click', '.delete-post', function(e){
				var info_id = $(this).attr('data-id');
				if(confirm('Apakah Anda akan menghapus artikel ini?'))
				{
					$.post('ajax-delete-informasi.php', {info_id:info_id, option:'delete'}, function(asnwer){
						window.location = 'informasi.php';
					});
				}
				e.preventDefault();
			});
			$(document).on('click', '.download-word', function(e){
				var title = $('.article-title').text();
				var content = $('.article-content').html();
				var creator = $('.article-creator').text();
				var html = '<div><h1>'+title+'</h1>\r\n<div>'+creator+'</div>'+ content+'</div>';
				var doc = $(html);
				doc = convertImagesToBase64(doc);
				var content = doc.html(); 
				var style = '<style type="text/css">body{font-family:"Times New Roman", Times, serif; font-size:16px; position:relative;} table[border="1"]{border-collapse:collapse; box-sizing:border-box; max-width:100%;} table[border="1"] td{padding:4px 5px;} table[border="0"] td{padding:4px 0;} p, li{line-height:1.5;} a{color:#000000; text-decoration:none;} h1{font-size:30px;} h2{font-size:26px;} h3{font-size:22px;} h4{font-size:16px;}</style>';
				content = '<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"><title>'+title+'</title>'+style+'</head><body style="position:relative;">'+content+'</body></html>';
				var converted = new Blob([content], {type:'text/html'});
				saveAs(converted, title+'.html');
				e.preventDefault();
			});
		});
		function convertImagesToBase64 (doc) {
			var regularImages = doc.find('img');
			var canvas = document.createElement('canvas');
			var ctx = canvas.getContext('2d');
			[].forEach.call(regularImages, function (obj) {
				var imgElement = obj;
				ctx.clearRect(0, 0, canvas.width, canvas.height);
				canvas.width = imgElement.width;
				canvas.height = imgElement.height;
				ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
				var dataURL = canvas.toDataURL();
				imgElement.setAttribute('src', dataURL);
				imgElement.style.width = canvas.width+'px';
				imgElement.style.maxWidth = '100%';
				imgElement.style.height = 'auto';
				imgElement.removeAttribute('height');
			});
			canvas.remove();
			return doc;
		}
		</script>
		<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
        <style type="text/css">
		.article-title h1{
			font-family:"Roboto";
			font-size:28px;
		}
		</style>
		<div class="article-title"><h3 data-active="<?php echo $data['active'];?>"><?php echo $data['name'];?></h3></div>
		<div class="article-content"><?php echo $data['content'];?></div>
		<div class="article-time">Dibuat <strong><?php echo $data['time_create'];?></strong></div>
		<div class="article-creator">Oleh <strong><?php echo $data['creator'];?></strong></div>
		<div class="article-link">
			<a href="<?php echo $picoEdu->gateBaseSelfName();?>">Lihat Semua</a>
			<a href="javascript:;" class="download-word">Download</a>
			<a href="informasi.php?option=edit&info_id=<?php echo $data['info_id'];?>">Ubah</a>
			<a class="delete-post" data-id="<?php echo $data['info_id'];?>" href="informasi.php?option=delete&info_id=<?php echo $data['info_id'];?>">Hapus</a>
		</div>
		<?php
	}
}
else
{
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Informasi</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";

if($pagination->getQuery()){
$pagination->appendQueryName('q');
$sql_filter .= " AND (`edu_info`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}


$nt = '';

$sql = "SELECT `edu_info`.*,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_info`.`admin_edit`) AS `admin_edit_name` 
FROM `edu_info`
WHERE (1=1) $sql_filter
ORDER BY `edu_info`.`info_id` DESC
";
$sql_test = "SELECT `edu_info`.*
FROM `edu_info`
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
<form name="form1" method="post" action="">

<div class="d-flex search-pagination search-pagination-top">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>

<form name="form1" method="post" action="">
<style type="text/css">
@media screen and (max-width:799px)
{
	.hide-some-cell tr td:nth-child(5), .hide-some-cell tr td:nth-child(6), .hide-some-cell tr td:nth-child(7){
		display:none;
	}
}
</style>
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table table-striped table-sm hide-some-cell">
  <thead>
    <tr>
      <td width="16"><input type="checkbox" name="control-info_id" id="control-info_id" class="checkbox-selector" data-target=".info_id" value="1"></td>
      <td width="16"><i class="fas fa-pencil"></i></td>
      <td width="25">No</td>
      <td>Judul Informasi</td>
      <td>Admin</td>
      <td width="130">Diubah</td>
      <td width="60">Aktif</td>
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
      <td><input type="checkbox" name="info_id[]" id="info_id" value="<?php echo $data['info_id'];?>" class="info_id" /></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=edit&info_id=<?php echo $data['info_id'];?>"><i class="fas fa-pencil"></i></a></td>
      <td align="right"><?php echo $no;?> </td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&info_id=<?php echo $data['info_id'];?>"><?php echo $data['name'];?></a></td>
      <td><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&info_id=<?php echo $data['info_id'];?>"><?php echo $data['admin_edit_name'];?></a></td>
      <td nowrap="nowrap"><a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=detail&info_id=<?php echo $data['info_id'];?>"><?php echo $data['time_edit'];?></a></td>
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
  <input type="submit" name="delete" id="delete" value="Hapus" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin akan menghapus baris yang dipilih?');" />
  <input type="button" name="add" id="add" value="Tambah" class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>?option=add'" />
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
<div class="alert alert-warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>