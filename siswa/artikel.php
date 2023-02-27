<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
require_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
$pageTitle = "Artikel";
$pagination = new \Pico\PicoPagination();

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
		$data = $stmt->fetch(\PDO::FETCH_ASSOC);
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
	?>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Informasi</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo $picoEdu->getSearchQueryFromUrl();?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn btn-success" />
</form>
</div>

<div class="search-result">
<?php

$sql_filter = "";

if($pagination->getQuery()){
	$pagination->appendQueryName('q');
	$sql_filter .= " AND (`edu_article`.`title` like '%".addslashes($pagination->getQuery())."%' )";
}

$nt = '';


	$sql = "SELECT `edu_article`.*, `member`.`name` AS `creator`
	FROM `edu_article` 
	LEFT JOIN (`member`) ON (`member`.`member_id` = `edu_article`.`member_create`) 
	WHERE `edu_article`.`school_id` = '$school_id' AND `edu_article`.`active` = true $sql_filter
	ORDER BY `edu_article`.`article_id` DESC
	";
	$sql_test = "SELECT `edu_article`.`article_id`
	FROM `edu_article` 
	WHERE `edu_article`.`school_id` = '$school_id' AND `edu_article`.`active` = true $sql_filter
	";
	
	$stmt = $database->executeQuery($sql_test);
	$pagination->setTotalRecord($stmt->rowCount());
	$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
	$pagination->setTotalRecordWithLimit($stmt->rowCount());
	if($pagination->getTotalRecordWithLimit() > 0)
	{
		$pagination->createPagination(basename($_SERVER['PHP_SELF']), true); 
		$paginationHTML = $pagination->buildHTML();	

		?>
		<div class="main-content">
			<div class="main-content-wrapper">
			<div class="article-list row">
		<?php
	$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	foreach($rows as $idx=>$data)
	{

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
		$content = trim($content);
		if($content == "" || $content == '&nbsp;' && isset($obj->img))
		{
			foreach($obj->img as $imgno=>$img)
			{
				$content = '<img src="'.$img['src'].'">';
			}
		}
		$cls = "";
		if($pagination->getTotalRecordWithLimit() % 2 == 1 && $idx == $pagination->getTotalRecordWithLimit() - 1)
		{
			$cls = " col-sm-12";
		}
		else
		{
			$cls = " col-sm-6";
		}
		?>

		<div class="article-item<?php echo $cls;?>">
			<div class="card h-100">
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
	</div>
	</div>

	<div class="d-flex search-pagination search-pagination-bottom">
<div class="col-md-6 col-sm-12 search-pagination-control"><?php echo $paginationHTML;?></div>
<div class="col-md-6 col-sm-12 search-pagination-label"><?php echo $pagination->getResultInfo();?></div>
</div>
	<?php
		
	}
	require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}

?>