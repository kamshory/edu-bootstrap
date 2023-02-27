<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/bukan-guru.php";
	exit();
}


$pagination = new \Pico\PicoPagination();
$pageTitle = "Infomasi";
if(!isset($school_id) || empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}

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
		$pageTitle = $data['name'];

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
			if($pos===false)
			{
				$pos = stripos($content, ".", $maxlen);
			}
			if($pos===false)
			{
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
		require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
		?>
		<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
        <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/info.js"></script>
        <style type="text/css">
		.article-title h1{
			font-family:"Roboto";
			font-size:28px;
		}
		</style>
        <div class="main-content">
            <div class="main-content-wrapper">
            <div class="article-title"><h1><?php echo $data['name'];?></h1></div>
            <div class="article-content"><?php echo $data['content'];?></div>
            <div class="article-time">Dibuat <?php echo translateDate(date(\Pico\PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?></div>
            <div class="article-creator">Oleh <?php echo $data['creator'];?></div>
			<div class="article-link button-area">
				<a class="btn btn-primary" href="javascript:;" class="download-word"><i class="fas fa-download"></i> Download</a>
				<a class="btn btn-primary" href="informasi.php"><i class="fas fa-book"></i> Lihat Semua</a>
			</div>
        </div>
        </div>
		<?php
		require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
	}
	else
	{
		require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
		require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
	}
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
	$sql_filter .= " AND (`edu_info`.`name` like '%".addslashes($pagination->getQuery())."%' )";
}

$nt = '';

$sql = "SELECT `edu_info`.*,
(SELECT `edu_admin`.`name` FROM `edu_admin` WHERE `edu_admin`.`admin_id` = `edu_info`.`admin_edit`) AS `admin_edit_name` 
FROM `edu_info`
WHERE (1=1) $sql_filter
ORDER BY `edu_info`.`info_id` DESC
";
$sql_test = "SELECT `edu_info`.*
FROM `edu_info`
WHERE (1=1) $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->setTotalRecord($stmt->rowCount());
$stmt = $database->executeQuery($sql . $pagination->getLimitSql());
$pagination->setTotalRecordWithLimit($stmt->rowCount());
if($pagination->getTotalRecordWithLimit() > 0)
{
	$pagination->createPagination($picoEdu->gateBaseSelfName(), true); 
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
				<h5 class="card-title"><?php echo $data['name'];?></h5>
				<p class="card-text"><?php echo $content;?></p>
				<div class="article-time">Dibuat <em><?php echo $data['time_create'];?></em></div>
				<div class="article-creator">Oleh <em><?php echo $data['admin_edit_name'];?></em></div>
				<div class="button-area">
				<a href="informasi.php?info_id=<?php echo $data['info_id'];?>" class="btn btn-primary"><i class="fas fa-book"></i> Selengkapnya</a>
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
else if(@$_GET['q'] != '')
{
?>
<div class="warning">Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.</div>
<?php
}
else
{
?>
<div class="warning">Data tidak ditemukan. <a href="<?php echo $picoEdu->gateBaseSelfName();?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>