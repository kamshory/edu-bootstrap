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
	$('.test-question > li').each(function(index, element) {
        $(this).attr('data-no', index+1).addClass('nomor');
    });
	$('.test-question img').each(function(index, element) {
		var no = $(this).closest('.nomor').attr('data-no');
		testImage($(this).attr('src'), no);
	});
	$(document).on('click', '.kd-ctrl a', function(e){
		var question_id = $(this).attr('data-question-id');
		var obj = $(this).find('span');
		var bc = obj.text().trim();
		obj.replaceWith('<input type="text" value="'+bc+'" data-question-id="'+question_id+'" class="kd-ctrl-input">');
		$(this).find('input').select();
		$(this).closest('.kd-ctrl').attr('data-focus', 'true');
		e.preventDefault();
	});
	$(document).on('blur', '.kd-ctrl-input', function(e){
		save_competence($(this));
	});
	$(document).on('submit', 'form[name="form1"]', function(e){
		e.preventDefault();
		save_competence($(this).find('.kd-ctrl-input'));
	});
	$(document).on('submit', 'form[name="formrandom"]', function(e){
		var data = {};
		$('form[name="formrandom"] tbody tr').each(function(index, element) {
			var tr = $(this);
			var bc = 'bc'+tr.find('.take').attr('data-basic-competence');
			var col = tr.find('.take').val();
			data[bc] = col;
        });
		var test_id = $('form[name="formrandom"] input[name="test_id"]').val(); 
		$.post('ajax-question-distribution.php', {'save':'save', test_id:test_id, data:JSON.stringify(data)}, function(answer){
		})
		closeOverlayDialog();
		e.preventDefault();
	});
	$(document).on('change blur keyup', 'form[name="formrandom"] .take', function(e){
		var total = 0;
		$('form[name="formrandom"] tbody tr').each(function(index, element) {
			var tr = $(this);
			var col = parseInt(tr.find('.take').val());
			total += col;
        });
		$('form[name="formrandom"] .take_total').val(total);
	});
}
function save_competence(obj)
{
	var question_id = obj.attr('data-question-id');
	var value = obj.val();
	var parent = obj.closest('.kd-ctrl');
	obj.replaceWith('<span data-question-id"'+question_id+'">'+value+'</span>');
	parent.attr('data-focus', 'false');
	$.post('ajax-update-competence.php', {save:'save', question_id:question_id, value:value}, function(answer){
	});
}
function testImage(url, no) {
    var tester = new Image();
    tester.addEventListener('error', function(){
	});
    tester.src = url;
}
function downloadInWord()
{
	var source = $('.test-question')[0].outerHTML;
	var doc = $(source);
	$(doc).find(' > li').each(function(index, element) {
        $(this).append("<br>");
    });
	$(doc).find('.question-edit-ctrl').remove();
	$(doc).find('.option-circle').remove();
	doc = convertImagesToBase64(doc);
	var content = doc[0].outerHTML;
	var converted = htmlDocx.asBlob('<!DOCTYPE html>'+content, {orientation: 'portrait'});
	saveAs(converted, test_name+'.docx');
}
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
	});
	canvas.remove();
	return doc;
}
function distribution(test_id)
{
	$.get('ajax-question-distribution.php', {test_id:test_id}, function(answer){
		var obj = $(answer);
		obj.css({'opacity':0});
		obj.css({'width':'300px'});
		$('body').append(obj);
		var heigth = obj.height();
		var html = '<div id="basic-competence-distribution"></div>';
		overlayDialog(html, 300, heigth);
		$('#basic-competence-distribution').empty().append(obj);
		obj.css({'opacity':1});
	});
}
