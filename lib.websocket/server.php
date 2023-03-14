<?php
$dbConfig = dirname(dirname(dirname(__FILE__)))."/db.ini";
date_default_timezone_set("Asia/Jakarta");

require_once dirname(__FILE__)."/lib/autoload.php"; //NOSONAR
require_once dirname(dirname(__FILE__))."/lib.inc/autoload.php"; //NOSONAR
require_once dirname(dirname(__FILE__))."/lib.config/ws-cfg.php"; //NOSONAR

$wsDatabaseCredentials = new \Pico\PicoDatabaseCredentials();
$wsDatabaseCredentials->load($dbConfig);

$app = new \WS\WSSessionParser();
$wsDatabase = new \WS\WSDatabase(
    $wsDatabaseCredentials->getDriver(), 
    $wsDatabaseCredentials->getHost(), 
    $wsDatabaseCredentials->getPort(), 
    $wsDatabaseCredentials->getUsername(), 
    $wsDatabaseCredentials->getPassword(), 
    $wsDatabaseCredentials->getDatabaseName(), 
    $wsDatabaseCredentials->getTimeZone()
);

$wss = new \WS\WSTestService(
    $wsDatabase, 
    $wsConfig->ws_host, 
    $wsConfig->ws_port, 
    $app, 
    'postConstructClient', 
    "WSServer started on port ".$wsConfig->ws_port."\r\n"
);
$ret = $wss->run();
