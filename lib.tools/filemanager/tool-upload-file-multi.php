<?php
include_once(__DIR__ . "/functions.php");
include_once __DIR__ . "/auth.php";
include __DIR__ . "/conf.php"; //NOSONAR
if ($fmanConfig->authentification_needed && !$userlogin) {
	exit();
}
if (!$fmanConfig->allow_upload_all_file && !$fmanConfig->allow_upload_image) {
	die('DENIED');
}
if ($fmanConfig->readonly) {
	die('READONLY');
}
$targetdir = path_decode(kh_filter_input(INPUT_GET, "targetdir"), $fmanConfig->rootdir);


if (isset($_FILES["images"]) && is_array($_FILES["images"]["error"])) {

	foreach ($_FILES["images"]["error"] as $key => $error) {
		if ($error == 0) {
			$name = $_FILES["images"]["name"][$key];
			$name = kh_filter_file_name_safe($name);
			$compressimage = @$_SESSION['compress-image-cb'];
			$settings['compressimageonupload'] = $compressimage;
			// if exist before, file will not be deleted
			$allowdelete = true;
			if (file_exists($targetdir . "/" . $name)) {
				$allowdelete = false;
			}
			if (isset($_FILES['images']['tmp_name'])) {
				if (is_uploaded_file($_FILES['images']['tmp_name'][$key])) {
					copy($_FILES['images']['tmp_name'][$key], $targetdir . "/" . $name);
				}
				move_uploaded_file($_FILES["images"]["tmp_name"][$key], $targetdir . "/" . $name);
				$fileSync->createFile($targetdir . "/" . $name, true);
				$info = getimagesize($targetdir . "/" . $name);
				compressImageFile($targetdir . "/" . $name, $authblogid);
				deleteForbidden($targetdir, $fileSync);
				if (stripos($info['mime'], 'image') !== false) {
					if (!$fmanConfig->allow_upload_image) {
						if ($allowdelete) {
							$fileSync->deleteFile($targetdir . "/" . $name, true);
						}
						die('FORBIDDEN');
					}
				} else if (!$fmanConfig->allow_upload_all_file) {
					if ($allowdelete) {
						$fileSync->deleteFile($targetdir . "/" . $name, true);
					}
					die('FORBIDDEN');
				}
			}
		}
	}
}

echo 'SUCCESS';
