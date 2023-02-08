function initTest()
{
	var i, question_id, answer_id;
	for(i = 0; i < questionSet.length; i++)
	{
		answerData[i] = {question:questionSet[i], read:0, answer:0, doubt:0};
	}
	loadAnswer();
}
function loadAnswer()
{
	var json = window.localStorage.getItem(storageKey+'-answer-set') || '';
	var currentIndexSaved = loadCurrentIndex();
	if(json != '')
	{
		var data = JSON.parse(json);
		if(data.length)
		{
			answerData = data;
			for(i = 0; i<answerData.length; i++)
			{
				question_id = answerData[i].question;
				answer_id = answerData[i].answer;
				$('#answer_'+question_id).val(answer_id);
			}
		}
	}
	if(currentIndexSaved != '0')
	{
		currentIndex = parseInt(currentIndexSaved);
	}
}
function saveAnswer()
{
	var json = JSON.stringify(answerData);
	window.localStorage.setItem(storageKey+'-answer-set', json);
}
function saveCurrentIndex()
{
	window.localStorage.setItem(storageKey+'-current-index', currentIndex);
}
function loadCurrentIndex()
{
	return window.localStorage.getItem(storageKey+'-current-index') || '0';
}
function createSidebarNumber()
{
	var i, number;
	$('.question-number').empty();
	for(i = 0, number = 1; i < questionSet.length; i++, number++)
	{
		$('.question-number').append('\r\n<li data-number="'+number+'"><a href="#"><span>'+number+'</span></a></li>')
	}
}
function renderSidebar()
{
	var i, number, obj;
	for(i = 0, number = 1; i < answerData.length; i++, number++)
	{
		obj = answerData[i];
		if(obj.read == 0)
		{
			$('.question-number li[data-number="'+number+'"]').removeClass('unread').addClass('unread');
		}
		else if(obj.read == 1)
		{
			$('.question-number li[data-number="'+number+'"]').removeClass('unread');
		}
		if(obj.doubt == 1)
		{
			$('.question-number li[data-number="'+number+'"]').removeClass('doubt').addClass('doubt');
		}
		else if(obj.doubt == 0)
		{
			$('.question-number li[data-number="'+number+'"]').removeClass('doubt');
		}
		if(obj.answer != 0)
		{
			$('.question-number li[data-number="'+number+'"]').removeClass('answered').addClass('answered');
		}
		else if(obj.answer == 0)
		{
			$('.question-number li[data-number="'+number+'"]').removeClass('answered');
		}
	}
	var doubtButtonValue = $('#doubt').val();
	doubtButtonValue = doubtButtonValue.replace(String.fromCharCode(9635)+' ', '');
	doubtButtonValue = doubtButtonValue.replace(String.fromCharCode(9634)+' ', '');
	if(answerData[currentIndex].doubt == 0)
	{
		doubtButtonValue = String.fromCharCode(9634)+' '+doubtButtonValue;
		$('.current-number-placeholder').removeClass('doubt');
	}
	else
	{
		doubtButtonValue = String.fromCharCode(9635)+' '+doubtButtonValue;
		$('.current-number-placeholder').removeClass('doubt').addClass('doubt');
	}
	$('.current-number').text(currentIndex+1);
	$('.question-number li').removeClass('current-index');
	$('.question-number li[data-number="'+(currentIndex+1)+'"]').removeClass('current-index').addClass('current-index');
	$('#doubt').val(doubtButtonValue);
}
function renderQuestion(index)
{
	try{
	$('.question-area').attr({'data-index': index, 'data-question-id': questionSet[index]}); 
	var questionStr = questionData[index].text;
	var optionList = questionData[index].options;
	var numbering = questionData[index].numbering;
	var optionStr = '';
	var i, option;
	for(i = 0; i<optionList.length; i++)
	{
		option = optionList[i];
		optionStr += '\r\n		<div class="option" data-option-id="'+option.option_id+'"><div class="option-ctrl"><a href="#">'+numberingList[numbering][i]+'</a></div> '+option.text+'</div>\r\n';
	}
	optionStr += '\r\n		<div class="option not-answer" data-option-id="0"><div class="option-ctrl"><a href="#">'+numberingList[numbering][i]+'</a></div> '+optionNotAnswer+'</div>\r\n';
	var html = 
	'	<div class="question">\r\n'+
	'		'+questionStr+
	'	</div>\r\n'+
	'	<div class="option-area">\r\n'+
	optionStr + 
	'	</div>\r\n';
	$('.question-area').empty().append(html);
	}
	catch(e)
	{
	}
}
function renderAnswer(index)
{
	var data = answerData[index];
	$('.option-area .option').removeClass('answered').removeClass('doubt');
	$('.option-area .option[data-option-id="'+data.answer+'"]').addClass('answered');
	if(data.answer != 0)
	{
		$('.current-number-placeholder').removeClass('answered').addClass('answered');
	}
	else
	{
		$('.current-number-placeholder').removeClass('answered');
	}
	if(data.doubt != 0)
	{
		$('.option-area .option[data-option-id="'+data.answer+'"]').removeClass('doubt').addClass('doubt');
	}
	else
	{
		$('.option-area .option[data-option-id="'+data.answer+'"]').removeClass('doubt');
	}
}
function selectQuestion(index)
{
	try{
	currentIndex = index;
	answerData[currentIndex].read = 1;
	saveAnswer();
	saveCurrentIndex();
	renderSidebar();
	renderQuestion(index);
	renderAnswer(index);
	}
	catch(e)
	{
	}
}
function selectAnswer(index, answerID)
{
	try{
	answerData[index].read = 1;
	answerData[index].answer = answerID;
	var question_id = answerData[index].question;
	var answer_id = answerData[index].answer;
	$('#answer_'+question_id).val(answer_id);
	saveAnswer();
	renderSidebar();
	renderAnswer(index);
	}
	catch(e)
	{
	}
}
function nextQuestion()
{
	if(currentIndex < (questionSet.length - 1))
	{
		currentIndex++;
		selectQuestion(currentIndex);
	}
}
function prevQuestion()
{
	if(currentIndex > 0)
	{
		currentIndex--;
		selectQuestion(currentIndex);
	}
}
function preloadImage(data)
{
	var i, j, k, l, imageSrc = [];
	for(i = 0; i < data.length; i++)
	{
		j = $('<div>'+data[i].text+'</div>');
		j.find('img').each(function(idx1, elem1){
			imageSrc.push($(this).attr('src'));
		});
		for(k = 0; k < data[i].options.length; k++)
		{
			l = $('<div>'+data[i].options[k].text+'</div>');
			l.find('img').each(function(idx2, elem2){
				imageSrc.push($(this).attr('src'));
			});
		}
	}
	var img = [];
	for(i = 0; i< imageSrc.length; i++)
	{
		j = imageSrc[i];
		img[i] = new Image();
		img[i].src = j;
	}
}
function submitTest()
{
	var doubt = checkDoubtAnswer();
	if(doubt == 0)
	{
		$('#testfrm').submit();
	}
	else
	{
		customAlert('Anda tidak bisa mengirim jawaban sekarang karena masih ada '+doubt+' jawaban ragu-ragu. Silakan buang centang jawaban ragu-ragu lalu coba lagi.', 'Tutup');
		$('.test-question-control').removeClass('hidden');
		$('.question-number .doubt.answered:first a').click();
	}
}
function customAlert(message, button1)
{
	button1 = button1 || 'Tutup';
	$('.dialog-mask, .dialog-container').remove();
	var html = 
	'<div class="dialog-mask"></div>\r\n'+
	'<div class="dialog-container">\r\n'+
	'	<div class="dialog-body">'+message+'</div>\r\n'+
	'	<div class="dialog-button-panel"><button data-dismiss="true">'+button1+'</button></div>\r\n'+
	'</div>\r\n';
	$('body').append(html);
	$('.dialog-button-panel button[data-dismiss="true"]').on('click', function(e){
		$('.dialog-mask, .dialog-container').remove();
	});
}
function customConfirm(message, callback1, button1, button2)
{
	button1 = button1 || 'Ya';
	button2 = button2 || 'Tidak';
	$('.dialog-mask, .dialog-container').remove();
	var html = 
	'<div class="dialog-mask"></div>\r\n'+
	'<div class="dialog-container">\r\n'+
	'	<div class="dialog-body">'+message+'</div>\r\n'+
	'	<div class="dialog-button-panel"><button data-button="1">'+button1+'</button> <button data-button="2" data-dismiss="true">'+button2+'</button></div>\r\n'+
	'</div>\r\n';
	$('body').append(html);
	$('.dialog-button-panel button[data-button="1"]').on('click', function(e){
		callback1();
		$('.dialog-mask, .dialog-container').remove();
	});
	$('.dialog-button-panel button[data-dismiss="true"]').on('click', function(e){
		$('.dialog-mask, .dialog-container').remove();
	});
}
function checkDoubtAnswer()
{
	var i, j, doubt = 0;
	for(i = 0; i<answerData.length; i++)
	{
		if(answerData[i].doubt == 1 && answerData[i].answer !=0 )
		{
			doubt++;
		}
	}
	return doubt;
}
$(document).ready(function(e) {
	preloadImage(questionData);
    initTest();
	createSidebarNumber();
	renderSidebar();
	selectQuestion(currentIndex);
	$(document).on('click', '#next', function(e){
		nextQuestion();
	});
	$(document).on('click', '#prev', function(e){
		prevQuestion();
	});
	$(document).on('click', '#doubt', function(e){
		if(answerData[currentIndex].doubt == 0)
		{
			answerData[currentIndex].doubt = 1;
		}
		else
		{
			answerData[currentIndex].doubt = 0;
		}
		saveAnswer();
		renderSidebar();
		renderAnswer(currentIndex);
	});
	$(document).on('click', '.question-number li a', function(e){
		var index = parseInt($(this).closest('li').attr('data-number'))-1;
		selectQuestion(index);
		e.preventDefault();
	});
	$(document).on('click', '.option-area a', function(e){
		selectAnswer(parseInt($(this).closest('.question-area').attr('data-index')), parseInt($(this).closest('.option').attr('data-option-id')));
		e.preventDefault();
	});
	$(document).on('click', '.test-question-control .before a', function(e){
		var obj = $(this).closest('.test-question-control');
		if(obj.hasClass('hidden'))
		{
			obj.removeClass('hidden');
		}
		else
		{
			obj.addClass('hidden');
		}
		e.preventDefault();
	});
	$(document).on('click', '#submit', function(e){
		customConfirm('Apakah Anda akan mengirimkan jawaban ujian ini sekarang?', function(){
			setTimeout(function(){
			submitTest();
			}, 10);
		}, 'Ya', 'Tidak');
	});
	$(document).on('click', '.alert-closer', function(e){
		hideAlert = true;
		$('.alert-placeholder').attr('data-hidden', 'true');
		e.preventDefault();
	});
	$(document).on('keydown', function(e){
		if(e.keyCode >= 65 && e.keyCode <= 80)
		{
			var index = e.keyCode - 64;
			$('.option-area .option:nth-child('+index+') .option-ctrl a').click();
			e.preventDefault();
		}
		if(e.keyCode >= 49 && e.keyCode <= 57)
		{
			var index = e.keyCode - 48;
			$('.question-number li:nth-child('+index+') a').click();
			e.preventDefault();
		}
		if(e.keyCode == 48)
		{
			$('.question-number li:nth-child(10) a').click();
			e.preventDefault();
		}
		if(e.keyCode == 'R'.charCodeAt(0))
		{
			$('input#doubt').click();
			e.preventDefault();
		}
		if(e.keyCode == 37)
		{
			prevQuestion();
			e.preventDefault();
		}
		if(e.keyCode == 39)
		{
			nextQuestion();
			e.preventDefault();
		}
		if(e.keyCode == 38)
		{
			if(currentIndex > 4)
			{
				currentIndex -= 5;
				selectQuestion(currentIndex);
			}
			e.preventDefault();
		}
		if(e.keyCode == 40)
		{
			if(currentIndex < (questionSet.length - 5) )
			{
				currentIndex += 5;
				selectQuestion(currentIndex);
			}
			e.preventDefault();
		}
	});
	$(document).on('contextmenu', function(e){
		e.preventDefault();
		e.stopPropagation();
	});
	
	var t = new Date();
	var s = parseInt(t.getTime()/1000);
	tval = due_time+s;
	intervalID = setInterval(
	function(){
		displayRemainingTime('.timer');
	},
	1000);
	displayRemainingTime('.timer');
});
function secondsToTime(secs)
{
    var hours = Math.floor(secs / (60 * 60));  
    var divisor_for_minutes = secs % (60 * 60);
    var minutes = Math.floor(divisor_for_minutes / 60);
    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);
	if(hours<10 && hours>=0) hours = '0'+hours;
	if(minutes<10 && minutes>=0) minutes = '0'+minutes;
	if(seconds<10 && seconds>=0) seconds = '0'+seconds;
	var obj = {
        "h": hours,
        "m": minutes,
        "s": seconds
    };
    return obj;
}
var hideAlert = false;
function displayRemainingTime(obj)
{
	var t = new Date();
	var s = parseInt(t.getTime()/1000);
	var secs = tval - s;
	if(autosubmit)
	{
		secs = secs - 10;
	}
	var minus = false;
	var ov = '';
	if(secs <= alert_time)
	{
		$('.timer-placeholder').attr('data-has-alert', 'true');
		if(!hideAlert)
		{
			$('.alert-placeholder').attr('data-hidden', 'false');
		}
	}
	if(secs < 10)
	{
		if(autosubmit)
		{
			document.testfrm.submit();
			clearInterval(intervalID);
		}
	}
	if(secs<0)
	{
		secs = -secs;
		minus = true;
		ov = 'Lewat ';
		$('.timer-placeholder').attr('data-time-over', 'true');
	}
	var tm = secondsToTime(secs);
	$(obj).text(ov+tm['h']+':'+tm['m']+':'+tm['s']);
}
var intervalID = null;
var tval;
var blankObject = {question:0, read:0, answer:0, doubt:0};
var answerData = [];
var currentIndex = 0;
var optionNotAnswer = 'Tidak menjawab';
var numberingList = {
	'upper-alpha':['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'],
	'lower-alpha':['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'],
	'upper-roman':['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'],
	'lower-roman':['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'vii', 'ix', 'x'],
	'decimal':['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
	'decimal-leading-zero':['01', '02', '03', '04', '05', '06', '07', '08', '09', '10']
};
