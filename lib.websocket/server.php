<?php
$dbConfig = dirname(dirname(dirname(__FILE__)))."/db.ini";
date_default_timezone_set("Asia/Jakarta");
require_once dirname(__FILE__)."/lib/autoload.php"; //NOSONAR
require_once dirname(dirname(__FILE__))."/lib.inc/autoload.php"; //NOSONAR

$configs = new \Pico\PicoDatabaseCredentials();
$configs->load($dbConfig);

$host = '127.0.0.1';
$port = 8888;

$app = new \WS\SessionParser();
$wsDatabase = new \WS\WSDatabase(
    $configs->getDriver(), 
    $configs->getHost(), 
    $configs->getPort(), 
    $configs->getUsername(), 
    $configs->getPassword(), 
    $configs->getDatabaseName(), 
    $configs->getTimeZone()
);

$wss = new \WS\WSTestService($wsDatabase, $host, $port, $app, 'postConstructClient', "Message started on port $port\r\n");
$ret = $wss->run();

