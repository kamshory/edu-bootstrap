$(document).ready(function(e) {
	$('input[type="datetime"]').each(function(index, element) {
        $(this).attr({'type':'text'}).addClass('input-text-datetime');
    });
	$('input[type="datetime-local"]').each(function(index, element) {
        $(this).attr({'type':'text'}).addClass('input-text-datetime');
    });
	$('input[type="date"]').each(function(index, element) {
        $(this).attr({'type':'text'}).addClass('input-text-date');
    });
	$('input[type="time"]').each(function(index, element) {
        $(this).attr({'type':'text'}).addClass('input-text-time');
    });
	$('.input-text-date').datepicker({dateFormat:'yy-mm-dd', changeMonth:true, changeYear:true});
	$('.input-text-time').timepicker({timeFormat:'hh:mm:ss', clockType:24});
	$('.input-text-datetime').datetimepicker({dateFormat:'yy-mm-dd', timeFormat:'hh:mm:ss', clockType:24, changeMonth:true, changeYear:true});
    
});
