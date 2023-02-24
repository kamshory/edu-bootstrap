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

$oneLevelUp = dirname(dirname(__FILE__));
$twoLevelUp = dirname(dirname(dirname(__FILE__)));

$configs = new stdClass();
$databaseConfigs = new stdClass();

$databaseConfigs->db_type = "mysql";
$databaseConfigs->db_host = "localhost";
$databaseConfigs->db_port = 3306;
$databaseConfigs->db_user = "root";
$databaseConfigs->db_pass = "alto1234";
$databaseConfigs->db_name = "mini_picopi";
$databaseConfigs->db_time_zone = "Asia/Jakarta";
$databaseConfigs->config_file = $twoLevelUp."/db.ini";

$configs->sync_database_application_dir = $oneLevelUp;
$configs->sync_database_base_dir = $oneLevelUp."/volume.sync/database/pool";
$configs->sync_database_pool_name = "pool";
$configs->sync_database_rolling_prefix = "poll_";
$configs->sync_database_extension = ".txt";
$configs->sync_database_maximum_length = 1000000;
$configs->sync_database_delimiter = '------------------------912284ba5a823ba425efba890f57a4e2c88e8369';

$configs->sync_file_application_dir = $oneLevelUp;
$configs->sync_file_base_dir = $oneLevelUp."/volume.sync/file/pool";
$configs->sync_file_pool_name = "pool";
$configs->sync_file_rolling_prefix = "poll_";
$configs->sync_file_extension = ".txt";
$configs->sync_file_maximum_length = 50000;
$configs->sync_file_use_relative_path = true;

$cfg = new stdClass();

$cfg->ws_port = 8888;

$cfg->base_url = "http://".$_SERVER['SERVER_NAME']."/edu-bootstrap/"; //NOSONAR
$cfg->base_assets = "http://".$_SERVER['SERVER_NAME']."/edu-bootstrap/"; //NOSONAR

$cfg->app_name = "Planet Edu";
$cfg->mail_noreply = "noreply@planetbiru.com";
$cfg->mail_update_profile = "noreply@planetbiru.com";
$cfg->main_url = "https://www.planetbiru.com";
$cfg->main_domain = "www.planetbiru.com";

$cfg->cdn_jquery = $cfg->base_assets."lib.assets/script/jquery/jquery.min.js";

$cfg->base_avatar = "http://".$_SERVER['SERVER_NAME']."/media.images/"; //NOSONAR
$cfg->base_images = "http://".$_SERVER['SERVER_NAME']."/media.edu/"; //NOSONAR
$cfg->meta_description = "Planet Edu merupakan sekolah virtual yang dapat dimanfaatkan oleh sekolah, guru dan siswa untuk pengayaan materi dan ujian online. Planet Edu bukanlan sistem informasi akademik karena sebenarnya Planet Edu merupakan media sosial yang mendukung pendidikan.";


$cfg->cdn_jquery = $cfg->base_assets."lib.assets/script/jquery/jquery.min.js";

/**
Old config
$cfg->base_avatar = "http://".$_SERVER['SERVER_NAME']."/media.images/";
$cfg->base_images = "http://".$_SERVER['SERVER_NAME']."/media.images/";
$cfg->base_url = 'http://'.$_SERVER['SERVER_NAME'].'/';
*/

$cfg->base_avatar = "/media.images/";
$cfg->base_images = "/media.images/";


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


$cfg->dec_precision = 2;// Old code getProfile('dec_precision', $cfg->dec_precision);
$cfg->dec_separator = ".";// Old code getProfile('dec_separator', $cfg->dec_separator);
$cfg->dec_thousands_separator = ",";// Old code getProfile('dec_thousands_separator', $cfg->dec_thousands_separator);

$cfg->dec_precision = 2;
$cfg->dec_separator = ".";
$cfg->dec_thousands_separator = ",";


$pageTitle = "";