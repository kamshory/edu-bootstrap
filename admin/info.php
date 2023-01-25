<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/login-form.php";
exit();
}
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";
$cfg->page_title = "Infomasi";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/login-form.php";
	exit();
}

if(isset($_GET['info_id']))
{
	$info_id = kh_filter_input(INPUT_GET, 'info_id', FILTER_SANITIZE_STRING_NEW);
	$sql_filter_info = " and `edu_info`.`info_id` = '$info_id' ";

	$sql = "SELECT `edu_info`.*, `member`.`name` as `creator`
	from `edu_info` 
	left join(`member`) on(`member`.`member_id` = `edu_info`.`admin_create`) 
	where `edu_info`.`active` = '1' $sql_filter_info ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$cfg->page_title = $data['name'];

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
			if($pos===false) $pos = $maxlen;
			$content = substr($content, 0, $pos+1);
			$content = tidyHTML($content);
		}
	
		$cfg->meta_description = htmlspecialchars(strip_tags($content));
		include_once dirname(__FILE__)."/lib.inc/header.php";
		?>
        <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
		<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
        <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/info.min.js"></script>
        <style type="text/css">
		.article-title h1{
			font-family:Roboto;
			font-size:28px;
		}
		</style>
        <div class="main-content">
            <div class="main-content-wrapper">
            <div class="article-title"><h1><?php echo $data['name'];?></h1></div>
            <div class="article-content"><?php echo $data['content'];?></div>
            <div class="article-time">Dibuat <?php echo translateDate(date('j F Y H:i:s', strtotime($data['time_create'])));?></div>
            <div class="article-creator">Oleh <?php echo $data['creator'];?></div>
            <div class="article-link">
            <a href="javascript:;" class="download-word">Download</a>
            <a href="info.php">Semua</a>
            </div>
        </div>
        </div>
		<?php
		include_once dirname(__FILE__)."/lib.inc/footer.php";
	}
	else
	{
		include_once dirname(__FILE__)."/lib.inc/header.php";
		include_once dirname(__FILE__)."/lib.inc/footer.php";
	}
}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<div class="search-control">
<form id="searchform" name="form1" method="get" action="">
    <span class="search-label">Informasi</span>
    <input type="text" name="q" id="q" autocomplete="off" class="input-text input-text-search" value="<?php echo htmlspecialchars(rawurldecode((trim(@$_GET['q']," 	
    "))));?>" />
    <input type="submit" name="search" id="search" value="Cari" class="com-button" />
</form>
</div>
<div class="search-result">
<?php
$sql_filter = "";
$pagination->array_get = array();
if($pagination->query){
$pagination->array_get[] = 'q';
$sql_filter .= " and (`edu_info`.`name` like '%".addslashes($pagination->query)."%' )";
}


$nt = '';

$sql = "SELECT `edu_info`.*,
(select `edu_admin`.`name` from `edu_admin` where `edu_admin`.`admin_id` = `edu_info`.`admin_edit`) as `admin_edit_name` 
from `edu_info`
where 1 $sql_filter
order by `edu_info`.`info_id` desc
";
$sql_test = "SELECT `edu_info`.*
from `edu_info`
where 1 $sql_filter
";
$stmt = $database->executeQuery($sql_test);
$pagination->total_record = $stmt->rowCount();
$stmt = $database->executeQuery($sql.$pagination->limit_sql);
$pagination->total_record_with_limit = $stmt->rowCount();
if($pagination->total_record_with_limit)
{
	$pagination->start = $pagination->offset+1;
	$pagination->end = $pagination->offset+$pagination->total_record_with_limit;
	
	$pagination->result = $picoEdu->createPagination(basename($_SERVER['PHP_SELF']), $pagination->total_record, $pagination->limit, $pagination->num_page, 
	$pagination->offset, $pagination->array_get, true, $pagination->str_first, $pagination->str_last, $pagination->str_prev, $pagination->str_next); 
	$pagination->str_result = "";
	foreach($pagination->result as $i=>$obj)
	{
	$cls = ($obj->sel)?" class=\"pagination-selected\"":"";
	$pagination->str_result .= "<a href=\"".$obj->ref."\"$cls>".$obj->text."</a> ";
	}
	
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
			if($pos===false) $pos = $maxlen;
			$content = substr($content, 0, $pos+1);
			$content = tidyHTML($content);
		}
	
		?>
		<div class="article-item">
			<div class="article-title"><h3><?php echo $data['name'];?></h3></div>
			<div class="article-content"><?php echo $content;?></div>
			<div class="article-link">
				<a href="info.php?option=detail&info_id=<?php echo $data['info_id'];?>">Baca</a>
			</div>
		</div>
		<?php
	}
	?>
    </div>
    </div>
</div>
<div class="search-pagination search-pagination-bottom">
<div class="search-pagination-control"><?php echo $pagination->str_result;?></div>
<div class="search-pagination-label"><?php echo $pagination->start;?>-<?php echo $pagination->end;?>/<?php echo $pagination->total_record;?></div>
</div>
<?php
}
else if(@$_GET['q'])
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
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>