<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
require_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
$pageTitle = "Artikel";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";

if(isset($_GET['article_id']))
{
	$article_id = kh_filter_input(INPUT_GET, "article_id", FILTER_SANITIZE_STRING_NEW);

	$sql = "SELECT `edu_article`.*, `member`.`name` AS `creator`
	FROM `edu_article` 
	LEFT JOIN (`member`) ON (`member`.`member_id` = `edu_article`.`member_create`) 
	WHERE `edu_article`.`article_id` = '$article_id' AND `edu_article`.`active` = true ";
	require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		?>
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
		<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
        <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/info.js"></script>
        <div class="article-title"><h3 data-active="<?php echo $data['active'];?>"><?php echo $data['title'];?></h3></div>
        <div class="article-content"><?php echo $data['content'];?></div>
        <div class="article-time">Dibuat <em><?php echo $data['time_create'];?></em></div>
        <div class="article-creator">Oleh <em><?php echo $data['creator'];?></em></div>
        <div class="article-link button-area">
            <a class="btn btn-primary" href="javascript:;" class="download-word"><i class="fas fa-download"></i> Download</a>
            <a class="btn btn-primary" href="artikel.php"><i class="fas fa-book"></i> Lihat Semua</a>
        </div>
        <?php
	}
	require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else
{
	require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$sql = "SELECT `edu_article`.*, `member`.`name` AS `creator`
	FROM `edu_article` 
	LEFT JOIN (`member`) ON (`member`.`member_id` = `edu_article`.`member_create`) 
	WHERE `edu_article`.`school_id` = '$school_id' AND `edu_article`.`active` = true
	ORDER BY `edu_article`.`article_id` DESC
	LIMIT 0, 10
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		?>
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
<style>
	.article-item{
		margin-bottom: 20px;
	}
	.card-text
	{
		position: relative;
	}
	.card-text img{
		max-width: 100%;
	}
</style>

        <div class="article-list row">
		
    <?php
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{

		$obj = parseHtmlData('<html><body>'.($data['content']).'</body></html>');
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
			$content = tidyHTML($content);
		}
		$content = trim($content);
		if($content == "" || $content == '&nbsp;' && isset($obj->img))
		{
			foreach($obj->img as $imgno=>$img)
			{
				$content = '<img src="'.$img['src'].'">';
			}
		}
	
		?>

		<div class="article-item col-sm-6">
			<div class="card">
				<div class="card-body d-flex flex-column align-items-stretch">
				<h5 class="card-title"><?php echo $data['title'];?></h5>
				<p class="card-text"><?php echo $content;?></p>
				<div class="article-time">Dibuat <em><?php echo $data['time_create'];?></em></div>
				<div class="article-creator">Oleh <em><?php echo $data['creator'];?></em></div>
				<div class="button-area">
				<a href="artikel.php?article_id=<?php echo $data['article_id'];?>" class="btn btn-primary"><i class="fas fa-book"></i> Selengkapnya</a>
				</div>
				</div>
			</div>
		</div>
		
		<?php
	}
	?>
	</div>
	<?php
		
	}
	require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}

?>