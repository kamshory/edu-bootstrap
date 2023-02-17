<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if($adminLoggedIn->admin_level != 1)
{
	require_once dirname(__FILE__)."/bukan-super-admin.php";
	exit();
}

if(@$_POST['option'] == 'upload-image')
{
	$avatar_dir = dirname(dirname(__FILE__)) . "/media.edu/user.avatar/admin/$admin_id";
	$dir2prepared = dirname(dirname(__FILE__)) . "/media.edu/user.avatar/admin/$admin_id";
	$dirBase = dirname(dirname(__FILE__));
	$permission = 0755;
	$fileSync->prepareDirectory($avatar_dir, $dirBase, $permission, true);	
	$base_src = "media.edu/user.avatar/admin/$admin_id";
	$path = $avatar_dir."/img-300x300.jpg";
	if (isset($_POST['image'])) {
        $path = $avatar_dir . "/img-300x300.jpg";
        $path2 = $avatar_dir . "/img-150x150.jpg";
 
        $img = $_POST['image'];
		$arr = explode(",", $img);
		$img = $arr[1];
		$jpeg = imagecreatetruecolor(300, 300);
        $jpeg2 = imagecreatetruecolor(150, 150);

        $white = imagecolorallocate($jpeg, 255, 255, 255);
		imagefilledrectangle($jpeg, 0, 0, 300, 300, $white);
		$png = imagecreatefromstring(base64_decode($img));
		imagecopy($jpeg, $png, 0, 0, 0, 0, 300, 300);
		

        imagejpeg($jpeg, $path, 70);
        $fileSync->createFile($path, true);      

        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $dst_width  = 150;
        $dst_height  = 150;
        $src_width  = 300;
        $src_height  = 300;
        
        imagecopyresized(
            $jpeg2,
            $jpeg,
            $dst_x,
            $dst_y,
            $src_x,
            $src_y,
            $dst_width,
            $dst_height,
            $src_width,
            $src_height
        );

        
        imagejpeg($jpeg2, $path2, 70);
		$fileSync->createFile($path2, true);

		$rand = sprintf("%06d", mt_rand(0, 999999));
        $sql = "UPDATE `edu_admin` SET `picture_rand` = '$rand' WHERE `admin_id` = '$admin_id' ";
        $database->executeUpdate($sql, true);
    }
	exit();
}

$pageTitle = "Foto";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

require_once dirname((__FILE__))."/lib.inc/header.php";
$nt = '';
$sql = "SELECT `edu_admin`.* $nt,
(SELECT `edu_admin1`.`name` FROM `edu_admin` AS `edu_admin1` WHERE `edu_admin1`.`admin_id` = `edu_admin`.`admin_create` limit 0,1) AS `admin_create`,
(SELECT `edu_admin2`.`name` FROM `edu_admin` AS `edu_admin2` WHERE `edu_admin2`.`admin_id` = `edu_admin`.`admin_edit` limit 0,1) AS `admin_edit`
FROM `edu_admin` 
WHERE `edu_admin`.`admin_id` = '$admin_id'
";
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$rand = $data['picture_rand'];
$avatar_url = "media.edu/user.avatar/admin/$admin_id/img-300x300.jpg?rand=$rand";
?>
  
  <script src="lib.assets/script/croppie.js"></script>
    <link rel="stylesheet" href="lib.assets/croppie.css" />
    <input type="file" name="upload" id="upload" style="position:absolute; left:-10000px; top:-10000px" />
    <div class="container">
        <div class="avatar-uploaded">
            <img src="<?php echo $avatar_url;?>" class="avatar img-circle img-thumbnail" alt="avatar">
            <div class="upload-controller">
                <a href="#" class="uploader-ctrl"><i class="fas fa-camera"></i></a>
            </div>
        </div>

        <div class="avatar-cropper">
            <div class="avatar-cropper-preview">
                <div id="image" style="margin-top:20px"></div>
            </div>
            <div class="avatar-cropper-preview">
                <button class="btn btn-success crop-image">Simpan</button>
                <button class="btn btn-warning">Batalkan</button>
            </div>
        </div>
	</div>
	<script>
		$(document).ready(function() {
			$image_crop = $('#image').croppie({
				enableExif: true,
				viewport: {
					width: 300,
					height: 300,
					type: 'square' //circle
				},
				boundary: {
					width: 320,
					height: 320
				},
				url: $('.avatar-uploaded > img').attr('src')
			});

			$(document).on('click', '.uploader-ctrl', function(e) {
				$('#upload').trigger('click');
				e.preventDefault();
			})

			$('#upload').on('change', function() {
				var reader = new FileReader();
				reader.onload = function(event) {
					$image_crop.croppie('bind', {
						url: event.target.result
					}).then(function() {
						console.log('jQuery bind complete');
					});
				}
				reader.readAsDataURL(this.files[0]);
				$('.avatar-uploaded').css({
					'display': 'none'
				});
				$('.avatar-cropper').css({
					'display': 'block'
				});
			});

			$('.crop-image').click(function(event) {
				$image_crop.croppie('result', {
					type: 'canvas',
					size: 'viewport'
				}).then(function(response) {

					$.ajax({
						url: 'foto.php',
						type: "POST",
						data: {
							"option": "upload-image",
							"image": response
						},
						success: function(data) {

						}
					});
				})
			});
		});
	</script>
<?php
}
require_once dirname((__FILE__))."/lib.inc/footer.php";

?>