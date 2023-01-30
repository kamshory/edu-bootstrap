let last_id = '';
$(document).ready(function(e) {
	$(document).on('change', '#gradefrm select', function(e){
		$(this).closest('form').submit();
	});
    $(document).on('click', '.file-list ul li a', function(e){
		$('.file-list ul li').removeClass('test-selected');
		$(this).parent().addClass('test-selected');
		let id = $(this).attr('data-id');
		$('.test-selector-container').fadeOut(10);
		if(id != last_id)
		{
			previewFile(id);
			last_id = id;
			$('.collection-preview-container').attr('data-id', id);
		}
		e.preventDefault();
	});
	$(document).on('click', '.select-existing', function(e){
		let obj = $(this);
		let left = obj.offset().left;
		let right = obj.width()+left;
		let center = ((right+left)/2);
		let top = obj.offset().top + obj.height() + 26;
		left = center - 240;
		$('.test-selector-container').css({'left':left, 'top':top}).fadeIn(100);
		e.preventDefault();
	});
	
	
	$(document).on('click', '.create-new', function(e){
		let obj = $(this);
		let selection = $('.file-preview').attr('data-selection') || '';
		let id = obj.closest('.collection-preview-container').attr('data-id');
		window.open('ujian.php?option=add&import=yes&collection='+id+'&selection='+selection);
		e.preventDefault();
	});
	
	$(document).on('click', '.close-test-selector a', function(e){
		$('.test-selector-container').fadeOut(100);
		e.preventDefault();	
	});
	$(document).on('click', '.import-question', function(e){
		if(confirm('Apakah Anda akan mengimpor soal ke ujian ini?'))
		{
			let to = $(this).attr('data-test-id');
			let from = $('.collection-preview-container').attr('data-id');
			let selection = $('.file-preview').attr('data-selection') || '';
			$.post('ajax-ujian-impor.php', {from:from, to:to, selection:selection}, function(answer){
				$('.test-selector-inner').empty().append(answer);
				alert('Soal ujian sudah diimpor');
			});
		}
		e.preventDefault();	
	});
	$(document).on('click', '.select-question', function(e){
		let sel = $(this).attr('data-selected') || 'false';
		if(sel == 'true')
		{
			sel = 'false';
		}
		else
		{
			sel = 'true';
		}
		$(this).attr('data-selected', sel);
		let json = [];
		$('.select-question').each(function(index, element) {
			let sel = $(this).attr('data-selected') || 'false';
            let idx = parseInt($(this).attr('data-index'));
			json[idx] = (sel=='true')?1:0;
        });
		let data = JSON.stringify(json);
		let col = $(this).closest('.question-text-area').attr('data-collection-id');
		storeSelection(col, data);
		e.preventDefault();	
	});
});
function previewFile(id)
{
	let i;
	$.ajax({
		'cache':true,
		'url':'ajax-preview-question-store.php', 
		'type':'GET',
		'dataType':'html',
		data:{id:id}, 
		success: function(answer){
			$('.file-preview').empty().append(answer);
			let arr = loadSelection(id);
			if(typeof arr != 'undefined')
			{
				if(arr != null)
				{
					for(i in arr)
					{
						if(arr[i]==0 || arr[i]=='0')
						{
							$('.select-question[data-index="'+i+'"]').attr('data-selected', 'false');
						}
						else
						{
							$('.select-question[data-index="'+i+'"]').attr('data-selected', 'true');
						}
					}
					$('.file-preview').attr('data-selection', JSON.stringify(arr));
				}
			}
		}
	});
}
function loadSelection(col)
{
	let data = window.localStorage.getItem('col'+col);
	let arr = JSON.parse(data);
	return arr;
}
function storeSelection(col, data)
{
	window.localStorage.setItem('col'+col, data);
	$('.file-preview').attr('data-selection', data);
}