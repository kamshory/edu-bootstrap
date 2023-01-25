var tval;
var data_answer = {};
var str_answer = '';
str_answer = window.localStorage.getItem('jwb_'+test) || '{}';
data_answer = JSON.parse(str_answer);
function setanswer()
{
	var i, j, k;
	for(i in data_answer)
	{
		j = i.substr(4);
		k = data_answer[i];
		var soal = j;
		var answer = k;
		loadanswer(soal, answer);
	}
}
function saveanswer(soal, answer)
{
	data_answer['jwb_'+soal] = answer;
	str_answer = JSON.stringify(data_answer);
	window.localStorage.setItem('jwb_'+test, str_answer);
	markanswer(soal, answer);
}
function loadanswer(soal, answer)
{
	$('input.radio_answer[data-question="'+soal+'"][value="'+answer+'"]').prop('checked', true);
	markanswer(soal, answer);
}
function markanswer(soal, answer)
{
	$('.circle-answer-area a[data-question="'+soal+'"] span').attr('data-answered', (answer)?'true':'false');
}
var intervalID;
window.onload=function(){
	setanswer();
	$(document).on('change', '.radio_answer', function(){
			var test = $(this).attr('data-test');
			var soal = $(this).attr('data-question') || 0;
			var fname = $(this).attr('name');
			var answer = $(this).attr('value');
			saveanswer(soal, answer);

	});
	var t = new Date();
	var s = parseInt(t.getTime()/1000);
	tval = due_time+s;
	displayRemainingTime('#sisa-waktu, #pringatan-sisa-waktu, #countdown-element-inner');
	intervalID = setInterval(
	function(){
		displayRemainingTime('#sisa-waktu, #pringatan-sisa-waktu, #countdown-element-inner');
	},
	1000);
	$(document).scroll(function(){
		updateControlPosition();													
	});
	$(window).resize(function(){
		updateControlPosition();													
	});
    $(document).on('click', '.pagination ul li a', function(e){
		var segmen = $(this).attr('data-segmen');
		$(this).closest('ul').find('li a').removeClass('page-selected');
		$(this).addClass('page-selected');
		
		$('[data-dibagi="1"] ol > li').css({'display':'none'});
		$('[data-dibagi="1"] ol > li[data-segmen="'+segmen+'"]').css({'display':''})
		var offset = $('#question-test-wrapper').offset().top;
		$(window).scrollTop(offset - 50);
		e.preventDefault();
	});
	$(document).on('click', '.tigger-check-answer', function(e){
		var segmen = $(this).attr('data-segmen');
		var soal = $(this).attr('data-question');
		var number = $(this).attr('data-number');
		$('.pagination ul li a[data-segmen="'+segmen+'"]').click();
		
		/*
		$('ol#test-question > li[data-question="'+soal+'"]').css('background-color', '#ddf7ff');
		$('ol#test-question > li[data-question="'+soal+'"]').animate({'background-color': '#FFFFFF'}, 2000, 'swing', function(e){
		});

		$('ol#test-question > li[data-question="'+soal+'"] .option-ctrl').css('background-color', '#2498ed');
		
		setTimeout(function(){
			$('ol#test-question > li[data-question="'+soal+'"] .option-ctrl').animate({'background-color': '#FFFFFF'}, 2000, 'swing', function(e){
		});
		}, 3000);
		*/
		
		
		var offset = $('#question-test-wrapper li[data-number="'+number+'"]').offset().top;
		$(window).scrollTop(offset - 50);
		mui.closePopUp('check-answer');
		
		e.preventDefault();
	});
}

function updateControlPosition(){
	var height=$(window).height();
	var width=$(window).width();
	var width2 = $('#countdown-element').width();
	var left = parseInt((width-width2)/2);
	$('#countdown-element').css('left',left+'px');
	
	var scrolltop=$(document).scrollTop();
	var top = scrolltop+4;
	
	$('#countdown-element').css({'position':'fixed', 'top':'4px'});
	if(scrolltop>150)
	{
		$('#countdown-element').css('display','block');
	}
	else
	{
		$('#countdown-element').css('display','none');
	}
	var opacity=1;
	if(scrolltop>350)
	{
		opacity=1;
	}
	else
	{
		opacity=((scrolltop-150)/200);
	}
	$('#countdown-element').css('opacity',opacity);
}

function displayRemainingTime(obj, obj2)
{
	var t = new Date();
	var s = parseInt(t.getTime()/1000);
	var secs = tval - s;
	var minus = false;
	var ov = '';
	if(secs <= alert_time)
	{
		$('.all').attr('data-has_alert', 'true');
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
		$(obj).css('color', '#EE0000');
	}
	var tm = secondsToTime(secs);
	$(obj).text(ov+tm['h']+':'+tm['m']+':'+tm['s']);
}
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
function submitTest(frm)
{
	mui.confirm('Apakah Anda yakin akan mengirimkan jawaban Anda?', function(){	
		frm.submit();
	},
	function(){}, 'Yakin', 'Tidak');
}
