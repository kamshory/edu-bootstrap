<?php

$testAnswer = $picoTest->getTestAnswer($studentLoggedIn, $eduTest);

$list = $picoTest->getQuestionList($studentLoggedIn, $eduTest, $testAnswer);

if($testAnswer == null)
{
    $testAnswer = $picoTest->createTestAnswer($studentLoggedIn, $eduTest, $list);
}

$question = $picoTest->getQuestion($list, $eduTest);
$testDataFinal = $picoTest->getTestData($eduTest, $question, $testAnswer);
$testDataJSON = json_encode($testDataFinal);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $eduTest->name;?></title>
    <base href="<?php echo $cfg->base_assets;?>siswa">
    <link rel="stylesheet" href="<?php echo $cfg->base_assets;?>lib.vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test-un-new.css">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="<?php echo $cfg->base_assets;?>lib.favs/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="<?php echo $cfg->base_assets;?>lib.favs/manifest.json">
    <link rel="mask-icon" href="<?php echo $cfg->base_assets;?>lib.favs/safari-pinned-tab.svg" color="#563d7c">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon.ico">
    <meta name="msapplication-config" content="<?php echo $cfg->base_assets;?>lib.favs/browserconfig.xml">
    <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.vendors/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        let testDataJSON = <?php echo $testDataJSON;?>;
        let testStudentId = '<?php echo $studentLoggedIn->student_id.$eduTest->test_id;?>';
		let testId = '<?php echo $eduTest->test_id;?>';
		let websocketURL = '<?php echo $picoEdu->getWebsocketHost();?>/?module=test&test_id='+testId;
        let sessionId = '<?php echo md5(session_id());?>';
	</script>
    <script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/test-un-new.js">
    </script>
</head>

<body className="snippet-body">
    <div class="wrapper">
        <nav aria-label="Sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3>UJIAN</h3>
                <hr>
            </div>
            <ul class="list-unstyled CTAs">
                <li> <a href="#" class="download">Subscribe</a> </li>
            </ul>
        </nav>
        <div class="content">
            <nav aria-label="Main Menu" class="navbar navbar-expand-lg navbar-light bg-light"> 
                <button type="button" id="sidebarCollapse"
                    class="btn btn-secondary"> <i class="fa fa-align-justify"></i> </button> <button class="navbar-toggler"
                    type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item active"> <a class="nav-link" href="siswa/">Depan</a></li>
                        <li class="nav-item"> <a class="nav-link" href="#">Informasi</a></li>
                        <li class="nav-item"> <a class="nav-link" href="siswa/logout.php">Keluar</a></li>
                    </ul>
                </div>
            </nav>
            <div class="content-wrapper">
                <div class="row">
                    <div class="col col-9 test-area">
                        
                        <div class="test-wrapper">
                            <div class="row test-status">
                                <div class="col col-6 text-left">Nomor : 1</div>
                                <div class="col col-6 text-right">Siswa Waktu: 12:00</div>
                            </div>

                            <div class="test-question-area">
                            </div>
                            <div class="test-option-area">                           
                            </div>                           
                        </div>
                        <div class="test-nav">
                            <a class="btn btn-primary button-prev" href="#">Sebelumnya</a>
                            <a class="btn btn-warning button-doubtful" href="#">Ragu</a>
                            <a class="btn btn-primary button-next" href="#">Sesudahnya</a>                               
                        </div>
                    </div>
                    <div class="col col-3 selector-area">                       
                        <div class="selector-wrapper">
                            <ul>                               
                            </ul>                         
                        </div>
                        <div class="button-area">
                            <button class="btn btn-danger button-help" data-type="medical"><i class="fas fa-suitcase-medical"></i></button>
                            <button class="btn btn-primary button-help" data-type="paper"><i class="fas fa-file"></i></button>
                            <button class="btn btn-primary button-help" data-type="pencil"><i class="fas fa-pencil"></i></button>
                            <button class="btn btn-primary button-help" data-type="toilet"><i class="fas fa-toilet"></i></button>
                            <button class="btn btn-primary button-help" data-type="help"><i class="fas fa-hand-paper"></i></button>
                            
                            <button class="btn btn-success">Kirim Hasil</button>
                        </div>
                    </div>
                </div>
                
                <div class="separator"></div>
                
            </div>
        </div>
    </div>
    <script type="text/javascript">
    
    
    </script>
	<script src="<?php echo $cfg->base_assets;?>lib.assets/script/test-ws.js"></script>
	<script src="<?php echo $cfg->base_assets;?>lib.assets/script/test-ws-student.js"></script>

	<div class="modal fade" id="test-alert" tabindex="-1" role="dialog" aria-labelledby="test-alert-title" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="test-alert-title">Pesan Pengawas</h5>
            </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
	</div>

    <div class="modal fade" id="test-confirm" tabindex="-1" role="dialog" aria-labelledby="test-alert-title" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="test-alert-title">Konfirmasi</h5>
                </div>
                <div class="modal-body">
                    Apakah Anda akan menyalin data jawaban dari server?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="copy-answer">Ya</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                </div>
            </div>
        </div>
	</div>

    <script type="text/javascript">
        var myLink = document.querySelectorAll('a[href="#"]');
        myLink.forEach(function (link) {
            link.addEventListener("click", function (e) {
                e.preventDefault();
            });
        });
    </script>
</body>

</html>