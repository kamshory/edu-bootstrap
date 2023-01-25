	function onChangeCountry()
	{
		var country_id = $('#country_id').val();
		var prefix = $('#country_id option:selected').attr('data-code');
		updatePhobeBackground(prefix);
		$('#phone_code').val(prefix);
		$.ajax({
			url:'../lib.ajax/ajax-load-state-list.php',
			type:'GET',
			dataType:"json",
			data:{country_id:country_id},
			success: function(data){
				$('#state_id').replaceWith('<select name="state_id" id="state_id" required="required" data-full-width="true"></select>');
				$('#state_id').empty();
				var html = '';
				html += '<option value="">- Pilih Provinsi -</option>';
				var i, j;
				for(i in data)
				{
					html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
				}
				if(allow_add_state)
				{
					html += '<option value="--">- Tambah Propinsi -</option>';
				}
				$('#state_id').append(html);
			}
		});
		$.ajax({
			url:'../lib.ajax/ajax-load-city-list.php',
			type:'GET',
			dataType:"json",
			data:{country_id:country_id},
			success: function(data){
				$('#city_id').replaceWith('<select name="city_id" id="city_id" required="required" data-full-width="true"></select>');
				$('#city_id').empty();
				var html = '';
				html += '<option value="">- Pilih Kabupaten/Kota -</option>';
				var i, j;
				for(i in data)
				{
					html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
				}
				if(allow_add_city)
				{
					html += '<option value="--">- Tambah Kota -</option>';
				}
				$('#city_id').append(html);
			}
		});
	}
	$(document).ready(function(e) {
		$(document).on('change', 'select#country_id', function(e){
			onChangeCountry();
		});
		$(document).on('change', 'select#state_id', function(e){
			var val = $(this).val();
			var _this = this;
			if(val == '--')
			{
				if(confirm('Apakah Anda akan mengubah jenis masukan?')){
					$(_this).replaceWith('<input type="text" name="state_id" id="state_id" required="required" data-full-width="true">');
					$('#state_id').select();
				}
			}
			else
			{
				var state_name = val;
				var country_id = $('#country_id').val();
				$.ajax({
					url:'../lib.ajax/ajax-load-city-list.php',
					type:'GET',
					dataType:"json",
					data:{state_name:state_name, country_id:country_id},
					success: function(data){
						$('#city_id').replaceWith('<select name="city_id" id="city_id" required="required" data-full-width="true"></select>');
						$('#city_id').empty();
						var html = '';
						html += '<option value="">- Pilih Kota -</option>';
						var i, j;
						for(i in data)
						{
							html += '<option value="'+data[i].l+'">'+data[i].l+'</option>';
						}
						if(allow_add_city)
						{
							html += '<option value="--">- Tambah Kota -</option>';
						}
						$('#city_id').append(html);
					}
				});
			}
		});
		$(document).on('change', 'select#city_id', function(e){
			var val = $(this).val();
			var _this = this;
			if(val == '--')
			{
				if(confirm('Apakah Anda akan mengubah jenis masukan?')){
					$(_this).replaceWith('<input type="text" name="city_id" id="city_id" required="required" data-full-width="true">');
					$('#city_id').select();
				}
			}
		});
		initRegisterForm();
	});


function updatePhobeBackground(code)
{
	var canvas = document.getElementById("phonebg");
	var ctx = canvas.getContext("2d");
	ctx.clearRect(0,0,40,36);

	ctx.beginPath();
	ctx.rect(0, 0, 40, 30);
	ctx.fillStyle = "#FAFAFA";
	ctx.fill();

	ctx.beginPath();
	ctx.fillStyle = "#333333";


	ctx.strokeStyle = '#888888';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(40, 0);
    ctx.lineTo(40, 29);
    ctx.stroke();

	ctx.font = "12px Arial";    
	ctx.textAlign = "end";      
	ctx.fillText(code, 36, 20);   
	var dataURL = canvas.toDataURL();  
	$('#phone').css({'background-image':'url('+dataURL+')', 'background-position':'-4px center', 'background-repeat':'no-repeat', 'padding-left':'40px'});             
}
if(typeof Language == 'undefined')
{
	var Language = {};
}
function initRegisterForm()
{
	$(document).on('blur', '.registerform input[name="username"]', function(){
		var that = $(this);
		var val = that.val();
		$.ajax({
			url:'../lib.ajax/ajax-check-username.php',
			data:{username:val},
			type:'POST',
			dataType:'json',
			success:function(data){
				if(!data.valid)
				{
					if(val != '')
					{
						alert('Email ini sudah terdaftar di Planetbiru.');
					}
					that.addClass('input-error');
					setTimeout(function(){
						that.val('');
					}, 2000);
				}
				else
				{
					if(!data.corrected)
					{
						that.addClass('input-error');	
					}
					else
					{
						that.val(data.corrected);
						that.removeClass('input-error');	
					}
					if(data.registered)
					{
						that.addClass('input-error');	
					}
					else
					{
						that.removeClass('input-error');	
					}
				}
			}
			
		});
	});
	$(document).on('blur', '.registerform input[name="email"]', function(){
		var that = $(this);
		var val = that.val();
		$.ajax({
			url:'../lib.ajax/ajax-check-email.php',
			data:{email:val},
			type:'POST',
			dataType:'json',
			success:function(data){
				if(data.registered)
				{
					if(val != '' && val.indexOf('@') != -1)
					{
						alert('Email ini sudah terdaftar di Planetbiru.');
					}
					that.addClass('input-error');	
					setTimeout(function(){
						that.val('');
					}, 2000);
				}
				else
				{
					that.removeClass('input-error');	
				}
			}
		});
	});
	$(document).on('blur', '.registerform input[name="name"]', function(){
		var that = $(this);
		var val = that.val();
		$.ajax({
			url:'../lib.ajax/ajax-check-name.php',
			data:{name:val},
			type:'POST',
			dataType:'json',
			success:function(data){
				if(!data.valid)
				{
					that.addClass('input-error');	
				}
				else
				{
					that.removeClass('input-error');	
				}
				that.val(data.corrected);
			}
		});
	});
}

function checkThisForm(frm){
	var that = $(frm);
	var validForm = 1;
	that.find(':input').each(function(index, element) {
		if($(this).hasClass('input-error'))
		{
			validForm = validForm * 0;
		}
		var required = $(this).is('[required]');
		if(required)
		{
			if($(this).is('select') || $(this).is('input[type="text"]') || $(this).is('input[type="password"]')  || $(this).is('input[type="text"]') )
			{
				if($(this).val() == '')
				{
					validForm = validForm * 0;
				}
			}
			if($(this).is('input[type="radio"]'))
			{
				if($(this).filter(':checked').val() == '')
				{  
					validForm = validForm * 0;
				}
			}
		}
	});
	if(!validForm)
	{
		alert('Isian salah.');
		return false;
	}
	return true;
}
