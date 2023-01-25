<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
if(empty(@$school_id))
{
include_once dirname(__FILE__)."/login-form.php";
exit();
}
$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
$sql = "select * from `edu_test` where `test_id` = '$test_id' and `school_id` = '$school_id' ";
$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
	$data = $stmt->fetch(PDO::FETCH_ASSOC);
$max_upload_file = ini_get('max_file_uploads');
if($max_upload_file == 0)
{
	$max_upload_file = 1;
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="msapplication-navbutton-color" content="#3558BE">
<meta name="theme-color" content="#3558BE">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="#3558BE">
<title><?php echo $cfg->app_name;?> Test Editor</title>
<link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/theme/default/css/test-editor.min.css">
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/jquery/jquery.min.js"></script>
<script type="text/javascript">
var maxUploadFile = <?php echo $max_upload_file;?>;
var startFrom = 1;
var parseImg = true;
var baseIMGURL = '<?php echo "media.edu/school/$school_id/test/$test_id/";?>';
var testID = '<?php echo $test_id;?>';
var equationRenderer = 'server';
var ua = window.navigator.userAgent.toString();
if(ua.indexOf('Firefox') !== -1)
{
	equationRenderer = 'browser';
}
var equationURLPreview = '<?php echo $cfg->equation_url_preview;?>';
var equationURLGenerator = '<?php echo $cfg->equation_url_generator;?>';
</script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/theme/default/js/test-maker.js"></script>
<script type="text/javascript" src="<?php echo $cfg->base_assets;?>lib.assets/script/FileSaver.js"></script>
<link rel="shortcut icon" type="image/jpeg" href="../favicon.ico"/>
</head>
<body>
<div id="all">
<div class="toolbar-area">
	<a class="toobar" id="toolbar-new" href="javascript:newFile()" title="Buat Ujian Baru"><span class="toolbar-icon toolbar-icon-new"></span></a>
	<a class="toobar" id="toolbar-save" href="javascript:saveFileAs()" title="Simpan Soal Ujian (Ctrl+S)"><span class="toolbar-icon toolbar-icon-save"></span></a>
	<a class="toobar" id="toolbar-open" href="javascript:openFile()" title="Buka Soal Ujian"><span class="toolbar-icon toolbar-icon-open"></span></a>
	<a class="toobar" id="toolbar-download" href="javascript:downloadFile()" title="Unduh Soal Ujian"><span class="toolbar-icon toolbar-icon-download"></span></a>
	<a class="toobar" id="toolbar-upload" href="javascript:uploadFile()" title="Unggah Soal Ujian"><span class="toolbar-icon toolbar-icon-upload"></span></a>
	<a class="toobar" id="toolbar-print" href="javascript:printFile(false)" title="Cetak Soal Ujian"><span class="toolbar-icon toolbar-icon-print"></span></a>
	<a class="toobar" id="toolbar-answer" href="javascript:printFile(true)" title="Cetak Soal Ujian dan Jawaban"><span class="toolbar-icon toolbar-icon-answer"></span></a>
	<a class="toobar" id="toolbar-delete" href="javascript:deleteFile()" title="Hapus Soal Ujian"><span class="toolbar-icon toolbar-icon-delete"></span></a>
	<a class="toobar" id="toolbar-enter" href="javascript:enterSign()" title="Turun Baris (Ctrl+Enter)"><span class="toolbar-icon toolbar-icon-enter"></span></a>
	<a class="toobar" id="toolbar-symbol" href="javascript:showSymbolDialog()" title="Masukkan Simbol"><span class="toolbar-icon toolbar-icon-symbol"></span></a>
	<a class="toobar" id="toolbar-subsuperscript" href="javascript:showSubSuperscriptDialog()" title="Subscript &amp; Superscript"><span class="toolbar-icon toolbar-icon-subsuperscript"></span></a>
	<a class="toobar" id="toolbar-latex" href="javascript:showLatexDialog()" title="Editor Latex (Ctrl+Q)"><span class="toolbar-icon toolbar-icon-latex"></span></a>
	<a class="toobar" id="toolbar-equation" href="javascript:showEquationDialog()" title="Masukkan Persamaan"><span class="toolbar-icon toolbar-icon-equation"></span></a>
	<a class="toobar" id="toolbar-chemistry-editor" href="javascript:showChemistryDialog()" title="Editor Molekul"><span class="toolbar-icon toolbar-icon-chemistry-editor"></span></a>
	<a class="toobar" id="toolbar-image" href="javascript:insertImage()" title="Masukkan Gambar"><span class="toolbar-icon toolbar-icon-image"></span></a>
	<a class="toobar" id="toolbar-audio" href="javascript:insertAudio()" title="Masukkan Suara"><span class="toolbar-icon toolbar-icon-audio"></span></a>
	<a class="toobar" id="toolbar-compress-audio" href="javascript:compressAudio()" title="Kompres Suara"><span class="toolbar-icon toolbar-icon-compress-audio"></span></a>
	<a class="toobar" id="toolbar-file-manager" href="lib.tools/filemanager/?test_id=<?php echo $test_id;?>" target="_blank" title="File Manager"><span class="toolbar-icon toolbar-icon-file-manager"></span></a>
	<a class="toobar" id="toolbar-html" href="ujian-soal.php?option=add&test_id=<?php echo $test_id;?>" title="Modus HTML" onclick="return confirm('Apakah Anda akan mengganti modus menjadi HTML?')"><span class="toolbar-icon toolbar-icon-html"></span></a>
	<a class="toobar" id="toolbar-append" href="javascript:appendToTest()" title="Masukkan Ke Dalam Ujian"><span class="toolbar-icon toolbar-icon-append"></span></a>
	<a class="toobar toolbar-selected" id="toolbar-restart" href="javascript:restartQuestion()" title="Sembunyikan Soal Sebelumnya"><span class="toolbar-icon toolbar-icon-restart"></span></a>
	<a class="toobar" id="toolbar-continue" href="javascript:continueQuestion()" title="Tampilkan Soal Sebelumnya"><span class="toolbar-icon toolbar-icon-continue"></span></a>
	<a class="toobar" id="toolbar-edit" href="javascript:toggleEditMode()" title="Mode Ubah"><span class="toolbar-icon toolbar-icon-edit"></span></a>
	<a class="toobar" id="toolbar-show-question" href="ujian-soal.php?test_id=<?php echo $test_id;?>" target="_blank" title="Tampilkan Semua Soal"><span class="toolbar-icon toolbar-icon-show-question"></span></a>
	<a class="toobar" id="toolbar-info" href="javascript:;" title="Informasi Ujian" data-test-id="<?php echo $test_id;?>"><span class="toolbar-icon toolbar-icon-info"></span></a>
</div>

	<div id="main">
        <div id="editor-area">
            <div id="editor-inner">
                <div class="title-bar"><?php echo $data['name'];?></div>
                <div class="progressbar-fixed"><div class="progressbar-fixed-inner"></div></div>
                <textarea id="input" name="input" spellcheck="false" placeholder="Tulis soal ujian di sini"></textarea>
            </div>
        </div>
        <div id="preview-area">
            <div id="preview-inner">
                <div class="title-bar">Tinjauan Soal</div>
                <div id="preview">
                    <div id="preview1"></div>
                    <div id="question-separator"></div>
                    <div id="preview2"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="footer">
<div id="status"></div>
</div>
<div id="dialog-area"></div>
<div class="frm1" id="frm1" style="width:1px; height:1px; position:absolute; left:-10000px; top:-10000px;">
<input type="file" id="file" name="file">
</div>
<div class="frm2" id="frm2" style="width:1px; height:1px; position:absolute; left:-10000px; top:-10000px;">
<input type="file" id="image" name="image">
</div>
<div class="frm3" id="frm3" style="width:1px; height:1px; position:absolute; left:-10000px; top:-10000px;">
<input type="file" id="audio" name="file" accept="audio/*">
<input type="file" id="audio2" name="file" accept="audio/*">
</div>
</body>
</html>
<?php
}
?>