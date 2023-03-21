<?php
include_once __DIR__."/inc-cfg.php";
$url = $_SERVER['REQUEST_URI'];
$arr = explode("?", $url, 2);
$latex = $arr[1];
$url = $cfg->equation_url_preview."?".$latex;
header("Content-Type: image/gif");
echo file_get_contents($url);
