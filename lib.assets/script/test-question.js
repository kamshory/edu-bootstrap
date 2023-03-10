document.addEventListener("DOMContentLoaded", function () {
	function setNoiseBackground(el, width, height, opacity) {
		let canvas = document.createElement("canvas");
		let context = canvas.getContext("2d");

		canvas.width = width;
		canvas.height = height;

		for (let i = 0; i < width; i++) {
			for (let j = 0; j < height; j++) {
				let val = Math.floor(Math.random() * 255);
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
			// DO nothing
		},
		stop: function(event, ui)
		{
			let array_question = [];
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
		let no = $(this).closest('.nomor').attr('data-no');
		testImage($(this).attr('src'), no);
	});
	$(document).on('click', '.kd-ctrl a', function(e){
		let question_id = $(this).attr('data-question-id');
		let obj = $(this).find('span');
		let bc = obj.text().trim();
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
		let data = {};
		$('form[name="formrandom"] tbody tr').each(function(index, element) {
			let tr = $(this);
			let bc = 'bc'+tr.find('.take').attr('data-basic-competence');
			let col = tr.find('.take').val();
			data[bc] = col;
        });
		let test_id = $('form[name="formrandom"] input[name="test_id"]').val(); 
		$.post('ajax-question-distribution.php', {'save':'save', test_id:test_id, data:JSON.stringify(data)}, function(answer){
		})
		closeOverlayDialog();
		e.preventDefault();
	});
	$(document).on('change blur keyup', 'form[name="formrandom"] .take', function(e){
		let total = 0;
		$('form[name="formrandom"] tbody tr').each(function(index, element) {
			let tr = $(this);
			let col = parseInt(tr.find('.take').val());
			total += col;
        });
		$('form[name="formrandom"] .take_total').val(total);
	});
}
function save_competence(obj)
{
	let question_id = obj.attr('data-question-id');
	let value = obj.val();
	let parent = obj.closest('.kd-ctrl');
	obj.replaceWith('<span data-question-id"'+question_id+'">'+value+'</span>');
	parent.attr('data-focus', 'false');
	$.post('ajax-update-competence.php', {save:'save', question_id:question_id, value:value}, function(answer){
	});
}
function testImage(url, no) {
    let tester = new Image();
    tester.addEventListener('error', function(){
	});
    tester.src = url;
}
function downloadInWord()
{
	let source = $('.test-question')[0].outerHTML;
	let doc = $(source);
	$(doc).find(' > li').each(function(index, element) {
        $(this).append("<br>");
    });
	$(doc).find('.question-edit-ctrl').remove();
	$(doc).find('.option-circle').remove();
	doc = convertImagesToBase64(doc);
	let content = doc[0].outerHTML;
	let converted = htmlDocx.asBlob('<!DOCTYPE html>'+content, {orientation: 'portrait'});
	saveAs(converted, test_name+'.docx');
}
function convertImagesToBase64 (doc) {
	let regularImages = doc.find('img');
	let canvas = document.createElement('canvas');
	let ctx = canvas.getContext('2d');
	[].forEach.call(regularImages, function (obj) {
		let imgElement = obj;
		ctx.clearRect(0, 0, canvas.width, canvas.height);
		canvas.width = imgElement.width;
		canvas.height = imgElement.height;
		ctx.drawImage(imgElement, 0, 0, imgElement.width, imgElement.height);
		let dataURL = canvas.toDataURL();
		imgElement.setAttribute('src', dataURL);
	});
	canvas.remove();
	return doc;
}
function distribution(test_id)
{
	$.get('ajax-question-distribution.php', {test_id:test_id}, function(answer){
		let obj = $(answer);
		obj.css({'opacity':0});
		obj.css({'width':'300px'});
		$('body').append(obj);
		let heigth = obj.height();
		let html = '<div id="basic-competence-distribution"></div>';
		overlayDialog(html, 300, heigth);
		$('#basic-competence-distribution').empty().append(obj);
		obj.css({'opacity':1});
	});
}
