function updateToggle()
{
	$('.toggle-tr').each(function(index, element) {
		let row = $(this)
        let sel = row.attr('data-toggle');
		$(':input[name="'+sel+'"]').change();
	});
}
function initToggle()
{
	$('.toggle-tr').each(function(index, element) {
		let row = $(this)
        let sel = row.attr('data-toggle');
		$(':input[name="'+sel+'"]').on('change', function(){
			let val = "0";
			if(($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio'))
			{
				if($(this)[0].checked)
				{
					val = $(this).attr('value');
				}
				else
				{
					val = "0";
				}
			}
			else
			{
				val = $(this).val();
			}
			if(!val) val = "0";
			if(val == row.attr('data-show-condition'))
			{
				row.css({'display':'table-row'});
			}
			if(val == row.attr('data-hide-condition'))
			{
				row.css({'display':'none'});
			}
		});
    });
}
function buildClassOption(list, value, schoolProgramId)
{
	let i;
	let j;
	let k;
	let html = '';
	let sel = '';
	let classArr = value.split(",");
	html += '<ul class="class-list">';
	
	for(i in list)
	{
		if(schoolProgramId == '' || schoolProgramId == list[i].school_program_id)
		{
			if($.inArray(list[i].class_id, classArr) != -1)
			{
				sel = ' checked="checked"';
			}
			else 
			{
				sel = '';
			}
			html += '<li class="class-item"><label><input class="form-check-input" type="checkbox" value="'+list[i].class_id+'"'+sel+'> <span>'+list[i].name+'</span></label></li>'; 
		}
	}
	
	html += '</ul>';
	return html;
}
function selectClass()
{
	let classListStr = $('#formedu_test #classlist').val();
	let schoolProgramId = $('#formedu_test #school_program_id').val();
	$('#class-list-container').empty().append(buildClassOption(classList, classListStr, schoolProgramId));
	$('#select-class-modal').modal('show');
}
$(document).ready(function(e) {
    setTimeout(function(){
		initToggle();
		updateToggle();
	}, 100);
    setTimeout(function(){
		let duration = parseInt(parseInt($('#duration').val() || '0')/60);
		$('#duration').val(duration);
	}, 500);
	$(document).on('click', '#select-class', function(e2){
		selectClass();
		e2.preventDefault();
	}); 

	$('#apply-class-list').on('click', function(e2){

	});

	$('#update-class').on('click', function(e){
		let arr = [];
		$('.class-list .class-item').each(function(index, element) {
			let input = $(this).find('input');
			if(input[0].checked)
			{
				arr.push(input.val());
			}
		});
		$('#formedu_test #classlist').val(arr.join(','));
		$('#select-class-modal').modal('hide');
	});
		
});
