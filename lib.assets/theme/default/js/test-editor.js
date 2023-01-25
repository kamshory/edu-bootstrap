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
var ascii_svg_server = 'lib.tools/asciisvg/svgimg.php';
var equation_preview_url = '../../../../../../cgi-bin/equgen.cgi?' ;
var equation_generator_url = '../../../../../../equgen.php?' ;
var equation_renderer_machine = (navigator.userAgent.toString().indexOf('Firefox') > -1)?'browser-png':'server-png';
var quran_server = '../quran';
$(document).ready(function() {
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
		content_css : "../lib.assets/theme/default/css/content-test-editor.css",
		
		file_browser_callback:"fileBrowserCallBack",
		
		apply_source_formatting:true,
		accessibility_warnings:false,

		// Drop lists for link/image/media/template dialogs
		//template_external_list_url : "lists/template_list.js",
		//external_link_list_url : "lists/link_list.js",
		//external_image_list_url : "lists/image_list.js",
		//media_external_list_url : "lists/media_list.js",

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
	

function simpansoal(frm){
	console.log('aaaaa')
	// cek isian
	var emptyeditor = 0;
	$('.htmleditor').each(function(index){
	var val = $(this).html();
	if(val=='')
	{
		emptyeditor++;
	}
	});
	
	if(emptyeditor>0)
	{
		alert('Soal dan option tidak boleh kosong.'+emptyeditor);
		return false;
	}
	
	var val_total = 0;
	var val_over = 0;
	$('.score').each(function(index){
	var val = parseInt($(this).val());
	if($(this).val() == '')
	{
		val = 0;
	}
	if(val > maxScore)
	{
		val_over++;
	}
	val_total += val;
	});
	
	if(val_over > 0)
	{
		alert('Nilai maksimum untuk setiap answer adalah '+maxScore);
		return false;
	}
	if(val_total == 0)
	{
		alert('Minimal ada sebuah pilihan yang mempunyai nilai.');
		return false;
	}
	
	var args = $(frm).serialize();
	var data = 'save=save&'+args;
	$.ajax({
		url:'ajax-add-question.php', 
		data:{'data':data}, 
		type:'POST',
		dataType:"json",
		success:function(obj){
			if(obj['duplicated']==0 || obj['duplicated']=='0')
			{
				$('.htmleditor').each(function(index){
				$(this).tinymce().execCommand('mceSetContent',false,'');
				});
				$('.score').val('');
				$('#total_collection').text(obj['collection']);
				$(document).scrollTop(0);
			}
			else
			{
				$('#total_collection').text(obj['collection']);
				alert('Soal yang sama untuk ujian ini telah dibuat sebelumnya. Silakan ubah soal dan jawaban.');
				$(document).scrollTop(0);
			}
		}
	});
	return false;
}