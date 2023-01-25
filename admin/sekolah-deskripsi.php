<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/bukan-admin.php";
	exit();
}
$cfg->module_title = "Keterangan Sekolah";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
if(isset($_POST['save']))
{
	$description = kh_filter_input(INPUT_POST, 'description');
	
	$base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/description";
	$base_src = "media.edu/school/$school_id/description";
	if(!file_exists($base_dir = dirname(dirname(__FILE__))."/media.edu"))
	{
		mkdir(dirname(dirname(__FILE__))."/media.edu/school", 0755);
	}
	if(!file_exists($base_dir = dirname(dirname(__FILE__))."/media.edu/$school_id"))
	{
		mkdir(dirname(dirname(__FILE__))."/media.edu/school/$school_id", 0755);
	}
	if(!file_exists($base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/description"))
	{
		mkdir(dirname(dirname(__FILE__))."/media.edu/school/$school_id/description", 0755);
	}
		
	$description = extractImageData($description, $base_dir, $base_src);
	
	$description = addslashes(UTF8ToEntities($description));
	
	$time_create = $time_edit = $picoEdu->getLocalDateTime();
	$admin_create = $admin_edit = $admin_login->admin_id;
	$ip_create = $ip_edit = $_SERVER['REMOTE_ADDR'];
	
	$sql = "update `edu_school` set
	`description` = '$description'
	where `school_id` = '$school_id'
	";
	$database->executeUpdate($sql);
	header("Location: ".basename($_SERVER['PHP_SELF']));
		
}

if(@$_GET['option']=='edit')
{
include_once dirname(__FILE__)."/lib.inc/header.php";

$state_list = array();
$city_list = array();
$sql = "SELECT `edu_school`.* 
from `edu_school` 
where 1
and `edu_school`.`school_id` = '$school_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<style type="text/css">
#description{
	width:100%;
	box-sizing:border-box;
	height:240px;
}
@media screen and (max-width:599px)
{
.button-area-responsive input[type="button"], .button-area-responsive input[type="submit"]{
	display:inline-block;
	width:100%;
	box-sizing:border-box;
	margin:4px 0;
}
}
</style>

<script type="text/javascript" src="lib.assets/script/tiny_mce/jquery.tinymce.js"></script>
<script type="text/javascript">
var base_assets = '<?php echo $cfg->base_assets;?>';
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
function getfileextension(filename){
return (/[.]/.exec(filename))?/[^.]+$/.exec(filename):'';
}
function removefileextension(filename){
return filename.replace(/\.[^/.]+$/,'');
}

var ascii_svg_server = 'lib.tools/asciisvg/svgimg.php';
var equation_preview_url = '../../../../../../cgi-bin/equgen.cgi?' ;
var equation_generator_url = '../../../../../../equgen.php?' ;
var equation_renderer_machine = (navigator.userAgent.toString().indexOf('Firefox') > -1)?'browser-png':'server-png';
var quran_server = '../quran';
$().ready(function() {
	$('textarea#description').tinymce({
		// Location of TinyMCE script
		script_url : 'lib.assets/script/tiny_mce/tiny_mce.js',

		// General options
		theme : "advanced",
        ascii_svg_server : ascii_svg_server,        
		equation_preview_url : equation_preview_url,        
		equation_generator_url : equation_generator_url, 
        equation_renderer_machine : equation_renderer_machine, 
		quran_server : quran_server, 
		plugins : "autolink,lists,style,table,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist,quran,latex,equation,chem,asciisvg,chart",
		theme_advanced_buttons1:"pasteword,pastetext,undo,redo,search,bold,italic,underline,strikethrough,formatselect,fontselect,fontsizeselect,justifyleft,justifycenter,justifyright,justifyfull,ltr,rtl,numlist,bullist,indent,outdent,blockquote",
		theme_advanced_buttons2:"visualaid,forecolor,backcolor,removeformat,anchor,link,unlink,image,media,charmap,quran,sup,sub,latex,equation,chem,asciisvg,chart,hr,table,row_props,cell_props,col_after,col_before,row_after,row_before,merge_cells,split_cells,delete_col,delete_row,delete_table,quran,arabiceditor,code,preview",
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

		// Replace values for the template plugin
		template_replace_values : {
			username : "Kamshory",
			staffid : "612126"
		}
	});
	setTimeout(function(){
		var iframe = document.getElementById('description_ifr');
		var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
		// innerDoc.addEventListener('paste', pasteHandler);
	}, 2000);
});
	
	
function fileBrowserCallBack(field_name, url, type, win)
{
	if(url.indexOf('data:') != -1)
	{
		url = '';
	}
	url = url.substr(21);
	
	var ajaxfilemanagerurl = "lib.tools/filemanager/?description=true&editor=tiny_mce&type="+type+"&field_name="+field_name+'&dir=base/'+dirname(url);
	switch (type){
		case "image":break;
		case "media":break;
		case "flash":break;
		case "file":break;
		default:
		return false;
	}
	tinyMCE.activeEditor.windowManager.open({url:ajaxfilemanagerurl,width:800,height:480,resizable:true,maximizable:true,inline:"yes",close_previous:"no"},{window:win,input:field_name});
}
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
</script>
<form name="formedu_school" id="formedu_school" action="" method="post" enctype="multipart/form-data">
<div class="input-block">
<textarea name="description" id="description"><?php echo htmlspecialchars($data['description']);?></textarea>
</div>
<div class="input-block button-area-responsive">
<input type="submit" name="save" id="save" class="com-button" value="Simpan" /> 
<input type="button" name="showall" id="showall" value="Kembali" class="com-button" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>'" />
</div>
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
$nt = '';
$sql = "SELECT `edu_school`.* $nt,
(select `country`.`name` from `country` where `country`.`country_id` = `edu_school`.`country_id`) as `country_id`,
(select `state`.`name` from `state` where `state`.`state_id` = `edu_school`.`state_id`) as `state_id`,
(select `city`.`name` from `city` where `city`.`city_id` = `edu_school`.`city_id`) as `city_id`
from `edu_school` 
where 1
and `edu_school`.`school_id` = '$school_id'
";
	$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<style type="text/css">
@media screen and (max-width:599px)
{
.button-area-responsive input[type="button"], .button-area-responsive input[type="submit"]{
	display:inline-block;
	width:100%;
	box-sizing:border-box;
	margin:4px 0;
}
}
</style>
<form name="formedu_school" action="" method="post" enctype="multipart/form-data">
<div class="page-title"><h3><?php echo $data['name'];?></h3></div>
<div class="page-content"><?php echo($data['description']!='')?$data['description']:'<p>[Tulis keterangan tentang sekolah. Klik tombol &quot;Ubah&quot; di bawah ini.]</p>';?></div>
<div class="input-block button-area-responsive">
<input type="button" name="edit" id="edit" class="com-button" value="Ubah" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?option=edit'" />
</div>
</form>
<?php
}
else
{
?>
<div class="warning">Anda tidak terdaftar sebagai Administrator sekolah. <a href="impor-data.php">Klik di sini untuk import data.</a></div>	
<?php
}
include_once dirname(__FILE__)."/lib.inc/footer.php";

}
?>