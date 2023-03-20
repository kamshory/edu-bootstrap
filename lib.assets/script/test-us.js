let tval;
let data_answer = {};
let str_answer = '';
str_answer = window.localStorage.getItem('jwb_'+test) || '{}';
data_answer = JSON.parse(str_answer);
function setanswer()
{
	let i, j, k;
	for(i in data_answer)
	{
		j = i.substring(4);
		k = data_answer[i];
		let soal = j;
		let answer = k;
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
let intervalID;
window.onload=function(){
	setanswer();
	$(document).on('change', '.radio_answer', function(){
			let test = $(this).attr('data-test');
			let soal = $(this).attr('data-question') || 0;
			let fname = $(this).attr('name');
			let answer = $(this).attr('value');
			saveanswer(soal, answer);

	});
	let t = new Date();
	let s = parseInt(t.getTime()/1000);
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
		let segmen = $(this).attr('data-segmen');
		$(this).closest('ul').find('li a').removeClass('page-selected');
		$(this).addClass('page-selected');
		
		$('[data-dibagi="1"] ol > li').css({'display':'none'});
		$('[data-dibagi="1"] ol > li[data-segmen="'+segmen+'"]').css({'display':''})
		let offset = $('#question-test-wrapper').offset().top;
		$(window).scrollTop(offset - 50);
		e.preventDefault();
	});
	$(document).on('click', '.tigger-check-answer', function(e){
		let segmen = $(this).attr('data-segmen');
		let soal = $(this).attr('data-question');
		let number = $(this).attr('data-number');
		$('.pagination ul li a[data-segmen="'+segmen+'"]').click();		
		
		let offset = $('#question-test-wrapper li[data-number="'+number+'"]').offset().top;
		$(window).scrollTop(offset - 50);
		mui.closePopUp('check-answer');
		
		e.preventDefault();
	});
}

function updateControlPosition(){
	let height=$(window).height();
	let width=$(window).width();
	let width2 = $('#countdown-element').width();
	let left = parseInt((width-width2)/2);
	$('#countdown-element').css('left',left+'px');
	
	let scrolltop=$(document).scrollTop();
	let top = scrolltop+4;
	
	$('#countdown-element').css({'position':'fixed', 'top':'4px'});
	if(scrolltop>150)
	{
		$('#countdown-element').css('display','block');
	}
	else
	{
		$('#countdown-element').css('display','none');
	}
	let opacity=1;
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
	let t = new Date();
	let s = parseInt(t.getTime()/1000);
	let secs = tval - s;
	let minus = false;
	let ov = '';
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
	let tm = secondsToTime(secs);
	$(obj).text(ov+tm['h']+':'+tm['m']+':'+tm['s']);
}
function secondsToTime(secs)
{
    let hours = Math.floor(secs / (60 * 60));  
    let divisor_for_minutes = secs % (60 * 60);
    let minutes = Math.floor(divisor_for_minutes / 60);
    let divisor_for_seconds = divisor_for_minutes % 60;
    let seconds = Math.ceil(divisor_for_seconds);
	if(hours<10 && hours>=0) hours = '0'+hours;
	if(minutes<10 && minutes>=0) minutes = '0'+minutes;
	if(seconds<10 && seconds>=0) seconds = '0'+seconds;
	let obj = {
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
