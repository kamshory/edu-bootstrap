<?php
/**
Old config
if(!defined('DB_HOST'))
define('DB_HOST', 'localhost');
if(!defined('DB_NAME'))
define('DB_NAME', 'mini_edu');
if(!defined('DB_USER'))
define('DB_USER', 'mini_edu');
if(!defined('DB_PASS'))
define('DB_PASS', '*Y8&76R46uyHuhUIiojY#4@r');
if(!defined('DB_PREF'))
define('DB_PREF', '');
*/

$cfg = new stdClass;
$configs = new stdClass;

$configs->db_type = "mysql";
$configs->db_host = "localhost";
$configs->db_port = 3306;
$configs->db_user = "root";
$configs->db_pass = "alto1234";
$configs->db_name = "mini_picopi";
$configs->db_time_zone = "Asia/Jakarta";
$configs->db_time_zone = "Asia/Jakarta";
$configs->sync_database_dir = dirname(dirname(__FILE__))."/lib.sync";

$cfg->base_url = "http://".$_SERVER['SERVER_NAME']."/edu-bootstrap/";
$cfg->base_assets = "http://".$_SERVER['SERVER_NAME']."/";
$cfg->app_name = "Planet Edu";
$cfg->mail_reset_password = "resetpassword@quliah.com";
$cfg->mail_invitation = "invitation@quliah.com";
$cfg->mail_noreply = "noreply@planetbiru.com";
$cfg->mail_update_profile = "noreply@planetbiru.com";
$cfg->main_url = "http://www.planetbiru.com";
$cfg->main_domain = "www.planetbiru.com";

$cfg->cdn_jquery = $cfg->base_assets."lib.assets/script/jquery/jquery.min.js";

$cfg->base_avatar = "http://".$_SERVER['SERVER_NAME']."/media.images/";
$cfg->base_images = "http://".$_SERVER['SERVER_NAME']."/media.images/";
$cfg->base_languages = "http://".$_SERVER['SERVER_NAME']."/lib.languages/";
$cfg->base_url = 'http://'.$_SERVER['SERVER_NAME'].'/';
$cfg->meta_description = "Planet Edu merupakan sekolah virtual yang dapat dimanfaatkan oleh sekolah, guru dan siswa untuk pengayaan materi dan ujian online. Planet Edu bukanlan sistem informasi akademik karena sebenarnya Planet Edu merupakan media sosial yang mendukung pendidikan.";

$cfg->base_url = "http://".$_SERVER['SERVER_NAME']."/";
$cfg->base_assets = "http://".$_SERVER['SERVER_NAME']."/";
$cfg->mail_reset_password = "resetpassword@quliah.com";
$cfg->mail_invitation = "invitation@quliah.com";
$cfg->mail_noreply = "noreply@planetbiru.com";
$cfg->mail_update_profile = "noreply@planetbiru.com";
$cfg->main_url = "http://www.planetbiru.com";
$cfg->main_domain = "www.planetbiru.com";

$cfg->cdn_jquery = $cfg->base_assets."lib.assets/script/jquery/jquery.min.js";

/**
Old config
$cfg->base_avatar = "http://".$_SERVER['SERVER_NAME']."/media.images/";
$cfg->base_images = "http://".$_SERVER['SERVER_NAME']."/media.images/";
$cfg->base_languages = "http://".$_SERVER['SERVER_NAME']."/lib.languages/";
$cfg->base_url = 'http://'.$_SERVER['SERVER_NAME'].'/';
*/

$cfg->base_url = "http://".$_SERVER['SERVER_NAME']."/edu-bootstrap/";
$cfg->base_assets = "http://".$_SERVER['SERVER_NAME']."/edu-bootstrap/";
$cfg->base_avatar = "/media.images/";
$cfg->base_images = "/media.images/";
$cfg->base_languages = "/lib.languages/";


$cfg->language_id = 'id';
date_default_timezone_set('Asia/Jakarta');

$cfg->numbering = array(
	"upper-alpha"=>array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'),
	"lower-alpha"=>array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'),
	"upper-roman"=>array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'),
	"lower-roman"=>array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'),
	"decimal"=>array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10'),
	"decimal-leading-zero"=>array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10')
);

$cfg->equation_url_preview = "http://".$_SERVER['HTTP_HOST']."/cgi-bin/equgen.cgi";
$cfg->equation_url_generator = "http://".$_SERVER['HTTP_HOST']."/equgen.php";

$cfg->max_invalid_signin_time = 420;
$cfg->max_invalid_signin_count = 3;

$pagination = new stdClass;
