<?php
date_default_timezone_set("Asia/Jakarta");
require_once dirname(__FILE__)."/lib/autoload.php";

$host = '127.0.0.1';
$port = 8888;

$app = new MyApp();

$wss = new WSBrokerService($host, $port, $app, 'prostConstructClient');
$ret = $wss->run();

