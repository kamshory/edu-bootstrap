<?php
date_default_timezone_set("Asia/Jakarta");
require_once dirname(__FILE__)."/lib/autoload.php"; //NOSONAR
require_once dirname(dirname(__FILE__))."/lib.inc/autoload.php"; //NOSONAR

$configs = (new \Pico\PicoDatabaseCredentials())->load(dirname(dirname(dirname(__FILE__)))."/db.ini");

$host = '127.0.0.1';
$port = 8888;

$app = new MyApp();
$wsDatabase = new \WSDatabase(
    $configs->getDriver(), 
    $configs->getHost(), 
    $configs->getPort(), 
    $configs->getUsername(), 
    $configs->getPassword(), 
    $configs->getDatabaseName(), 
    $configs->getTimezone()
);

$wss = new \WSBrokerService($wsDatabase, $host, $port, $app, 'prostConstructClient', "Message started on port $port\r\n");
$ret = $wss->run();

