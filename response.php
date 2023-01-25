<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
$cfg->page_title = "Tanggapan Sekolah";
include_once dirname(__FILE__)."/lib.assets/theme/default/header-home.php";

$sql = "select `edu_school_response`.* 
from `edu_school_response` 
where `edu_school_response`.`active` = '1' 
order by `edu_school_response`.`time` desc
";

$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	?>
    <div class="main-content">
    	<div class="main-content-wrapper">
            <h1>Tanggapan Sekolah Tentang <?php echo $cfg->app_name;?></h1>
	<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
	<div class="article-list">
	<?php
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($rows as $data)
	{

	
		?>
		<div class="article-item" style="margin-bottom:10px; border-bottom:1px dotted #777777;">
			<div class="article-title"><h3><?php echo $data['school'];?></h3></div>
			<div class="article-content"><?php echo $data['content'];?></div>
			<div class="article-time"><?php echo translateDate(date('j F Y H:i', strtotime($data['time'])));?> WIB</div>
		</div>
		<?php
	}
	?>
    </div>
    </div>
</div>
	<?php	
}
include_once dirname(__FILE__)."/lib.assets/theme/default/footer-home.php";
?>