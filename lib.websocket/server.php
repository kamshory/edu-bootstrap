<?php
date_default_timezone_set("Asia/Jakarta");
require_once dirname(__FILE__)."/lib/autoload.php"; //NOSONAR

$host = '127.0.0.1';
$port = 8888;

$app = new MyApp();
$wsDatabase = new \WSDatabase("mysql", "localhost", 3306, "root", "alto1234", "mini_picopi", "Asia/Jakarta");

$wss = new \WSBrokerService($wsDatabase, $host, $port, $app, 'prostConstructClient', "Message started on port $port\r\n");
$ret = $wss->run();

