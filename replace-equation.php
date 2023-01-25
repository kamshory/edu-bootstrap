<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Replace Equation</title>
<script type="text/javascript" src="http://localhost/edu/lib.assets/script/jquery/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(e) {
    $('img').each(function(index, element) {
        var src = $(this).attr('src');
		if(src.indexOf('codecogs') > -1)
		{
			urls.push(src);
//			var option_id = $(this).closest('.container').attr('id');
//			$.post('download-image.php',  {url:src, option_id:option_id}, function(answer){
//				$('#logs').append('<div>'+answer+'</div>');
//			});
		}
    });
	//walk();
});
var urls = [];
var i = 0;
function walk()
{
	if(i < urls.length)
	{
		var img = new Image();
		var canvas = document.createElement('canvas');
		img.onload = function(){
			canvas.setAttribute('width', img.width);
			canvas.setAttribute('height', img.height);
			var context = canvas.getContext('2d');
			context.drawImage(img, 0, 0);
			uploadImage(canvas.toDataURL('png'));
			walk();
		};
		img.src = urls[i];
	}
}
function uploadImage(data)
{
	console.log(data);
}

</script>
</head>

<body>
<?php

$sql = "select `edu_option`.*, (select `edu_question`.`test_id` from `edu_question` where `edu_question`.`question_id` = `edu_option`.`question_id`) as `test_id` 
from `edu_option` where `content` like '%codecogs%' order by `option_id` asc limit 0,100 ";

$sql = "select `edu_question`.* 
from `edu_question` where `content` like '%codecogs%'  ";

$stmt = $database->executeQuery($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $data)
{
?>
<div class="container" >
<div>Ujian <?php echo $data['test_id'];?> | Soal <?php echo $data['question_id'];?></div>
<?php echo $data['content'];?>
</div>
<?php
}
?>

<div id="logs"></div>
</body>
</html>