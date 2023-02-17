<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}

if(@$_POST['option'] == 'upload-image')
{
	$avatar_dir = dirname(dirname(__FILE__)) . "/media.edu/user.avatar/teacher/$teacher_id";
	$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/user.avatar/teacher/$teacher_id";
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirectory($avatar_dir, $dirBase, $permission, true);	
	$base_src = "media.edu/user.avatar/teacher/$teacher_id";
	$path = $avatar_dir."/img-300x300.jpg";
	if(isset($_POST['image']))
	{
		$img = $_POST['image'];
		$arr = explode(",", $img);
		$img = $arr[1];

		$png = imagecreatefromstring(base64_decode($img));
		imagejpeg($png, $path, 70);
		$fileSync->createFile($path, true);
	}
	exit();
}

$pageTitle = "Foto";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

require_once dirname((__FILE__))."/lib.inc/header.php";
$nt = '';
$sql = "SELECT `edu_teacher`.* , `edu_school`.`name` AS `school_name`
FROM `edu_teacher` 
LEFT JOIN (`edu_school`) ON (`edu_school`.`school_id` = `edu_teacher`.`school_id`)
WHERE `edu_teacher`.`school_id` = '$school_id'
AND `edu_teacher`.`teacher_id` = '$teacher_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
  
<style>
	#uploaded{
		margin: auto;
		width:320px;
		position: relative;
	}
	#uploaded .upload-controller{
		position: absolute;
		bottom:10px;
		right:30px;
	}
	#uploaded .upload-controller i{
		color: #FCFCFC;
		font-size: 2rem;
		text-shadow: 0px 0px 2px #CCCCCC;
	}
	#cropper{
		display: none;
		width:320px;
		margin: auto;
	}
	.cr-slider-wrap{
		margin: auto;
	}
	.cropper-preview{
		text-align: center;
	}
</style>

	<script src="lib.assets/script/croppie.js"></script>
	<link rel="stylesheet" href="lib.assets/croppie.css" />
	<input type="file" name="upload" id="upload" style="position:absolute; lert:-10000px; top:-10000px" />
	<div class="container">

		<div id="uploaded">
			<img src="media.edu/user.avatar/teacher/<?php echo $teacher_id;?>/img-300x300.jpg" class="avatar img-circle img-thumbnail" alt="avatar">
			<div class="upload-controller">
				<a href="#" class="uploader-ctrl"><i class="fas fa-camera"></i></a>
			</div>
		</div>

		<div id="cropper">
			<div class="cropper-preview">
				<div id="image" style="margin-top:20px"></div>
			</div>
			<div class="cropper-preview">
					<button class="btn btn-success crop_image">Crop & Upload Image</button>
			</div>
		</div>

<script>  
$(document).ready(function(){

	$image_crop = $('#image').croppie({
    enableExif: true,
    viewport: {
      width:300,
      height:300,
      type:'square' //circle
    },
    boundary:{
      width:320,
      height:320
    },
	
	url:'https://ssl.gstatic.com/accounts/ui/avatar_2x.png'
	
  });

  $(document).on('click', '.uploader-ctrl', function(e){
	$('#upload').trigger('click');
	e.preventDefault();
  })

  $('#upload').on('change', function(){
    var reader = new FileReader();
    reader.onload = function (event) {
      $image_crop.croppie('bind', {
        url: event.target.result
      }).then(function(){
        console.log('jQuery bind complete');
      });
    }
    reader.readAsDataURL(this.files[0]);
	$('#uploaded').css({'display':'none'});
	$('#cropper').css({'display':'block'});
  });



  $('.crop_image').click(function(event){
    $image_crop.croppie('result', {
      type: 'canvas',
      size: 'viewport'
    }).then(function(response){
		
      $.ajax({
        url:'foto.php',
        type: "POST",
        data:{"option":"upload-image",
			"image": response
		},
        success:function(data)
        {
          
        }
      });
    })
  });

});  
</script>

<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali.</a></div>	
<?php
}
require_once dirname((__FILE__))."/lib.inc/footer.php";

?>