<?php

$oneLevelUp = dirname(dirname(__FILE__));
$twoLevelUp = dirname(dirname(dirname(__FILE__)));
require_once dirname(__FILE__)."/ws-cfg.php";

$syncConfigs = new stdClass();
$databaseConfigs = new stdClass();

$databaseConfigs->db_type = "mysql";
$databaseConfigs->db_host = "localhost";
$databaseConfigs->db_port = 3306;
$databaseConfigs->db_user = "root";
$databaseConfigs->db_pass = "alto1234";
$databaseConfigs->db_name = "mini_picopi";
$databaseConfigs->db_time_zone = "Asia/Jakarta";
$databaseConfigs->config_file = $twoLevelUp."/db.ini";

$syncConfigs->sync_database_application_dir = $oneLevelUp;
$syncConfigs->sync_database_base_dir = $oneLevelUp."/volume.sync/database/pool";
$syncConfigs->sync_database_pool_name = "pool";
$syncConfigs->sync_database_rolling_prefix = "poll_";
$syncConfigs->sync_database_extension = ".txt";
$syncConfigs->sync_database_maximum_length = 1000000;
$syncConfigs->sync_database_delimiter = '------------------------912284ba5a823ba425efba890f57a4e2c88e8369';

$syncConfigs->sync_file_application_dir = $oneLevelUp;
$syncConfigs->sync_file_base_dir = $oneLevelUp."/volume.sync/file/pool";
$syncConfigs->sync_file_pool_name = "pool";
$syncConfigs->sync_file_rolling_prefix = "poll_";
$syncConfigs->sync_file_extension = ".txt";
$syncConfigs->sync_file_maximum_length = 50000;
$syncConfigs->sync_file_use_relative_path = true;


$cfg = new stdClass();
$cfg->app_code = "picoedu";
$cfg->ws_port = $wsConfig->ws_port;

$cfg->base_url = "http://".$_SERVER['SERVER_NAME']."/edu-bootstrap/"; //NOSONAR
$cfg->base_assets = "http://".$_SERVER['SERVER_NAME']."/edu-bootstrap/"; //NOSONAR

$cfg->app_name = "Planet Edu";
$cfg->main_domain = "www.planetbiru.com";
$cfg->meta_description = "Planet Edu merupakan sekolah virtual yang dapat dimanfaatkan oleh sekolah, guru dan siswa untuk pengayaan materi dan ujian online. Planet Edu bukanlan sistem informasi akademik karena sebenarnya Planet Edu merupakan media sosial yang mendukung pendidikan.";

$cfg->equation_url_preview = "http://".$_SERVER['HTTP_HOST']."/cgi-bin/equgen.cgi";
$cfg->equation_url_generator = "http://".$_SERVER['HTTP_HOST']."/equgen.php";

$cfg->max_invalid_signin_time = 420;
$cfg->max_invalid_signin_count = 3;

$cfg->dec_precision = 2;
$cfg->dec_separator = ".";
$cfg->dec_thousands_separator = ",";
$cfg->sync_data_enable = true;
$cfg->sync_time_enable = false;
$cfg->image_not_exported = array();
$cfg->audio_not_exported = array();

$pageTitle = "";