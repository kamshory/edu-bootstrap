<?php
require_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty($school_id))
{
	require_once dirname(__FILE__)."/login-form.php";
	exit();
}
require_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
require_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
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
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$pageTitle = $data['name'];

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
	
		$cfg->meta_description = htmlspecialchars(strip_tags($content));
		require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
		?>
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
		<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
        <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/info.js"></script>
        <style type="text/css">
		.article-title h1{
			font-family: "Roboto";
			font-size:28px;
		}
		</style>
        <div class="main-content">
            <div class="main-content-wrapper">
            <div class="article-title"><h1><?php echo $data['name'];?></h1></div>
            <div class="article-content"><?php echo $data['content'];?></div>
            <div class="article-time">Dibuat <?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?></div>
            <div class="article-creator">Oleh <?php echo $data['creator'];?></div>
            <div class="article-link">
            <a href="javascript:;" class="download-word">Download</a>
            <a href="informasi.php">Semua</a>
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
require_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Informasi</span>
    <input type="text" name="q" id="q" autocomplete="off" class="form-control input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
    "))));?>" />
    <input type="submit" name="search" id="search" value="Cari" class="btn com-button btn-success" />
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
	
	
	
	$pagination->createPagination(basename($_SERVER['PHP_SELF']), true); 
	$paginationHTML = $pagination->buildHTML();
	
	?>
    <div class="main-content">
    	<div class="main-content-wrapper">
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
        <div class="article-list">
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
	
		?>
		<div class="article-item">
			<div class="article-title"><h3><?php echo $data['name'];?></h3></div>
			<div class="article-content"><?php echo $content;?></div>
			<div class="article-link">
				<a href="informasi.php?option=detail&info_id=<?php echo $data['info_id'];?>">Baca</a>
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
<div class="warning">Data tidak ditemukan. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?option=add">Klik di sini untuk membuat baru.</a></div>
<?php
}
?>
</div>

<?php
require_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
?>