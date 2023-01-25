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
$(document).ready(function() {
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
	setTimeout(function(){
		var iframe = document.getElementById('content_ifr');
		var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
		// innerDoc.addEventListener('paste', pasteHandler);
	}, 2000);
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
		if(confirm('Anda harus menyimpan artikel ini terlebih dahulu sebelum melanjutkan. Apakah Anda akan menyimpan artikel ini sekarang?'))
		{
			$('#articleform').append('<input type="hidden" name="draff" value="Draff">').submit();
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
		
		var ajaxfilemanagerurl = "lib.tools/filemanager/?article_id="+article_id+"&editor=tiny_mce&type="+type+"&field_name="+field_name+'&dir=base/'+dirname(url);
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
			var image = '<img src="' + source + '" data-mce-selected="1"></img>';
			window.tinyMCE.execCommand('mceInsertContent', false, image);
		}
	}
}
function buildClassOption(list, value){
	var i, j, k;
	var html = '';
	var sel = '';
	var vals = value.split(",");
	html += '<ul class="class-list">';
	
	for(i in list)
	{
		if($.inArray(list[i].class_id, vals) != -1)
		{
			sel = ' class-item-selected';
		}
		else 
		{
			sel = '';
		}
		html += '<li class="class-item'+sel+'" data-class-id="'+list[i].class_id+'"><a href="javascript:;">'+list[i].name+'</a></li>'; 
	}
	
	html += '</ul>';
	return html;
}
function selectClass()
{
	var val = $('#articleform #classlist').val();
	var html = ''+
	'<div class="overlay-dialog-area">\r\n'+
	'	<h3>Select Class</h3>\r\n'+
	'    <div class="select-class-area">\r\n'+
	buildClassOption(classList, val)+
	'    </div>\r\n'+
	'    <div class="button-area" style="text-align:center">\r\n'+
	'    	<input type="button" class="com-button" id="update-class" value="Terapkan" />\r\n'+
	'    	<input type="button" class="com-button" id="cancel-class" value="Batalkan" />\r\n'+
	'    </div>\r\n'+
	'</div>\r\n';
	overlayDialog(html, 400, 360);
	$('.class-item').each(function(index, element) {
        $(this).find('a').on('click', function(e){
			$(this).parent().toggleClass('class-item-selected');
			e.preventDefault();
		});
    });
	$('#update-class').on('click', function(e){
		var arr = [];
		$('.class-item').each(function(index, element) {
			if($(this).hasClass('class-item-selected'))
			{
				arr.push($(this).attr('data-class-id'));
			}
		});
		$('#articleform #classlist').val(arr.join(','));
		closeOverlayDialog();
	});
	$('#cancel-class').on('click', function(e){
		closeOverlayDialog();
	});
}