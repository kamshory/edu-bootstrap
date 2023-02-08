<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-siswa.php";
if(empty(@$school_id))
{
	include_once dirname(__FILE__)."/login-form.php";
	exit();
}
include_once dirname(dirname(__FILE__))."/lib.inc/dom.php";
$cfg->page_title = "Artikel";
include_once dirname(dirname(__FILE__))."/lib.inc/cfg.pagination.php";



if(isset($_GET['article_id']))
{
	$article_id = kh_filter_input(INPUT_GET, "article_id", FILTER_SANITIZE_STRING_NEW);

	$sql = "SELECT `edu_article`.*, `member`.`name` AS `creator`
	FROM `edu_article` 
	LEFT JOIN (`member`) ON (`member`.`member_id` = `edu_article`.`member_create`) 
	WHERE `edu_article`.`article_id` = '$article_id' AND `edu_article`.`active` = true ";
	include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
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
        <div class="article-time">Dibuat <strong><?php echo $data['time_create'];?></strong></div>
        <div class="article-creator">Oleh <strong><?php echo $data['creator'];?></strong></div>
        <div class="article-link">
            <a href="javascript:;" class="download-word">Download</a>
            <a href="artikel.php">Lihat Semua</a>
        </div>
        <?php
	}
	include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}
else
{
	include_once dirname(__FILE__)."/lib.inc/header.php"; //NOSONAR
	$sql = "SELECT `edu_article`.*, `member`.`name` AS `creator`
	FROM `edu_article` 
	LEFT JOIN (`member`) ON (`member`.`member_id` = `edu_article`.`member_create`) 
	WHERE `edu_article`.`school_id` = '$school_id' AND `edu_article`.`active` = true
	ORDER BY `edu_article`.`article_id` DESC
	";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		?>
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
                <div class="article-title"><h3 data-active="<?php echo $data['active'];?>"><?php echo $data['title'];?></h3></div>
                <div class="article-content"><?php echo $content;?></div>
                <div class="article-time">Dibuat <strong><?php echo $data['time_create'];?></strong></div>
                <div class="article-creator">Oleh <strong><?php echo $data['creator'];?></strong></div>
                <div class="article-link">
                	<a href="artikel.php?article_id=<?php echo $data['article_id'];?>">Baca</a>
                    <?php
					if(@$auth_teacher_id && @$auth_teacher_school_id && @$auth_teacher_school_id == @$data['school_id'] && @$auth_teacher_id = @$data['member_create'])
					{
						?>
                        <a href="artikel.php?option=edit&article_id=<?php echo $data['article_id'];?>">Ubah</a>
                        <a class="delete-post" data-id="<?php echo $data['article_id'];?>" href="artikel.php?option=delete&article_id=<?php echo $data['article_id'];?>">Hapus</a>
                        <?php
					}
					?>
                </div>
            </div>
            <?php
		}
		?>
        </div>
        <?php
			
	}
	include_once dirname(__FILE__)."/lib.inc/footer.php"; //NOSONAR
}

?>