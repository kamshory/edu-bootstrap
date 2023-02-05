<?php
include_once dirname(__FILE__)."/lib.inc/functions-pico.php";
include_once dirname(__FILE__)."/lib.inc/sessions.php";
include_once dirname(__FILE__)."/lib.inc/dom.php";
$cfg->page_title = "Infomasi";
if(isset($_GET['info_id']))
{
	$info_id = kh_filter_input(INPUT_GET, "info_id", FILTER_SANITIZE_STRING_NEW);
	$sql_filter_info = " and `edu_info`.`info_id` = '$info_id' ";

	$sql = "SELECT `edu_info`.*, `member`.`name` as `creator`
	FROM `edu_info` 
	left join(`member`) on(`member`.`member_id` = `edu_info`.`admin_create`) 
	WHERE `edu_info`.`active` = true $sql_filter_info ";
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$cfg->page_title = $data['name'];

		$obj = parsehtmldata('<html><body>'.($data['content']).'</body></html>');
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
		?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Planetbiru">
    <meta name="generator" content="Planetbiru">
    <title>Pico Edu</title>


    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_url;?>lib.vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">

    <!-- Bootstrap core CSS -->
    <link href="<?php echo $cfg->base_assets;?>lib.vendors/bootstrap/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <!-- Custom styles for this template -->
    <link rel="stylesheet" type="text/css" href="lib.style/album.css">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="favs/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="favs/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="favs/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="favs/manifest.json">
    <link rel="mask-icon" href="favs/safari-pinned-tab.svg" color="#563d7c">
    <link rel="icon" href="favs/favicon.ico">
    <meta name="msapplication-config" content="favs/browserconfig.xml">
    <meta name="theme-color" content="#563d7c">



</head>

<body>

    <header>
        <div class="collapse bg-dark" id="navbarHeader">
            <div class="container">
                <div class="row">
                    <div class="col-sm-8 col-md-7 py-4">
                        <h4 class="text-white">Tentang Perangkat</h4>
                        <p class="text-muted">
                            Pico Edu adalah perangkat pintar yang dapat beroperasi secara offline 100%.
                            Perangkat ini dapat digunakan sebagai server aplikasi pendidikan khususnya pembelajaran elektronik.
                            Perangkat ini tidak membutuhkan internet dan dapat dijalankan dengan menggunakan bank daya portabel.
                        </p>
                    </div>
                    <div class="col-sm-4 offset-md-1 py-4">
                        <h4 class="text-white">Bantuan</h4>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-white">Channel YouTube</a></li>
                            <li><a href="#" class="text-white">Market Place</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="navbar navbar-dark bg-dark shadow-sm">
            <div class="container d-flex justify-content-between">
                <a href="#" class="navbar-brand d-flex align-items-center">
                    <strong>Pico Edu</strong>
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader"
                    aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </header>

    <main role="main">

        

        <div class="album py-5 bg-light">
            <div class="container">

                <div class="">

					<div class="article-title"><h1><?php echo $data['name'];?></h1></div>
					<div class="article-content"><?php echo $data['content'];?></div>
					<div class="article-time">Dibuat <?php echo translateDate(date(PicoConst::SHORT_DATE_TIME_INDONESIA_FORMAT, strtotime($data['time_create'])));?></div>
					<div class="article-creator">Oleh <?php echo $data['creator'];?></div>
					<div class="article-link">
						<button class="btn btn-success download-word">Download</button>
						<button class="btn btn-primary" onclick="window.location='informasi.php';">Semua</button>
					</div>
				</div>
            </div>
        </div>


        

        

    </main>

    <footer class="text-muted">
        <div class="container">
            <p class="float-right">
                <a href="#">Back to top</a>
            </p>
            <p>Album example is &copy; Bootstrap, but please download and customize it for yourself!</p>
            <p>New to Bootstrap? <a href="/">Visit the homepage</a> or read our <a
                    href="/docs/4.6/getting-started/introduction/">getting started guide</a>.</p>
        </div>
    </footer>



    <script src="lib.vendors/jquery/jquery.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="lib.vendors/jquery/jquery.min.js"><\/script>')</script>
    '
    <script src="lib.vendors/bootstrap/bootstrap.bundle.min.js"
        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct"
        crossorigin="anonymous"></script>


    <script src="lib.vendors/dashboard/feather.min.js"></script>
    <script src="lib.vendors/dashboard/Chart.min.js"></script>
    <script src="lib.vendors/dashboard/dashboard.js"></script>

</body>

</html>
		<?php
	}
	else
	{
		include_once dirname(__FILE__)."/lib.inc/header-bootstrap.php";
		include_once dirname(__FILE__)."/lib.inc/footer-bootstrap.php";
	}
}
else
{
include_once dirname(__FILE__)."/lib.inc/header-bootstrap.php";
$sql_filter_info = "";
if(isset($_GET['period']))
{
$period = kh_filter_input(INPUT_GET, "period", FILTER_SANITIZE_STRING_NEW);
$sql = "SELECT `edu_info`.* 
FROM `edu_info` 
WHERE `edu_info`.`active` = true and `edu_info`.`time_create` like '$period%' $sql_filter_info 
ORDER BY `edu_info`.`info_id` desc
";
}
else
{
$sql = "SELECT `edu_info`.* 
FROM `edu_info` 
WHERE `edu_info`.`active` = true $sql_filter_info 
ORDER BY `edu_info`.`info_id` desc
limit 0,20
";
}
$stmt = $database->executeQuery($sql);
if($stmt->rowCount() > 0)
{
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	?>
    <div class="main-content">
    	<div class="main-content-wrapper">
            <h1>Informasi</h1>
	<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">
	<div class="article-list">
	<?php
	foreach($rows as $data)
	{

		$pars = extractParagraph($data['content']);
		foreach($pars as $txt)
		{
			if(!empty($txt))
			{
				$content = $txt;
				$content = preg_replace('/[\s]+/', ' ', $content);
				if(strlen($content) > 100)
				{
					$content = substr($content, 0, 100)."&hellip;";
				}
			}
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
	<?php	
}

include_once dirname(__FILE__)."/lib.inc/footer-bootstrap.php";
}
?>