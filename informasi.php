<?php
include_once __DIR__."/lib.inc/functions-pico.php";
include_once __DIR__."/lib.inc/sessions.php";

$pageTitle = "Infomasi";
if(isset($_GET['info_id']))
{
	$info_id = kh_filter_input(INPUT_GET, "info_id", FILTER_SANITIZE_STRING_NEW);
	$sql_filter_info = " AND `edu_info`.`info_id` = '$info_id' ";

	$sql = "SELECT `edu_info`.*, `member`.`name` AS `creator`
	FROM `edu_info` 
	LEFT JOIN (`member`) ON (`member`.`member_id` = `edu_info`.`admin_create`) 
	WHERE `edu_info`.`active` = true $sql_filter_info ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);

		$obj = \Pico\PicoDOM::parseHtmlData('<html><body>'.($data['content']).'</body></html>');
		$arrparno = array();
		$arrparlen = array();
		$cntmax = ""; // do not remove
		$content = ""; // do not remove
		$i = 0;
		$minlen = 300;
		
		if(isset($obj->p) && count($obj->p)>0)
		{
			$max = 0;
			foreach($obj->p as $parno=>$par)
			{
				$arrparlen[$i] = strlen($par);
				if($arrparlen[$i]>$max)
				{
					$max = $arrparlen[$i];
					$cntmax = $par;
				}
				if($arrparlen[$i] >= $minlen)
				{
					$content = $par;
					break;
				}
			}
			if(!$content)
			{			
				$content = $cntmax;
			}
		}
		if(!$content)
		{
			$content = "&nbsp;";
		}
		$maxlen = 300;
		if(strlen($content)>$maxlen)
		{
			$content.=" ";
			$pos = stripos($content, ". ", $maxlen);
			if($pos===false){
			$pos = stripos($content, ".", $maxlen);
			}
			if($pos===false){
			$pos = stripos($content, " ", $maxlen);
			}
			if($pos===false) 
			{
				$pos = $maxlen;
			}
			$content = substr($content, 0, $pos+1);
			$content = \Pico\PicoDOM::tidyHTML($content);
		}
	
		$cfg->meta_description = htmlspecialchars(strip_tags($content));
		include_once __DIR__."/lib.inc/header-bootstrap.php";
		?>
                <div class="">
					<div class="article-title"><h1><?php echo $data['name'];?></h1></div>
					<div class="article-content"><?php echo $data['content'];?></div>
					<div class="article-time">Dibuat <?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?></div>
					<div class="article-creator">Oleh <?php echo $data['creator'];?></div>
					<div class="article-link">
						<button class="btn btn-success download-word">Download</button>
						<button class="btn btn-primary" onclick="window.location='<?php echo $picoEdu->gateBaseSelfName();?>';">Semua</button>
					</div>
				</div>            
		<?php
		include_once __DIR__."/lib.inc/footer-bootstrap.php";
	}
	else
	{
		include_once __DIR__."/lib.inc/header-bootstrap.php";
		include_once __DIR__."/lib.inc/footer-bootstrap.php";
	}
}
else
{
	include_once __DIR__."/lib.inc/header-bootstrap.php";
	include_once __DIR__."/lib.inc/inc-informasi.php";
	include_once __DIR__."/lib.inc/footer-bootstrap.php";
}
?>