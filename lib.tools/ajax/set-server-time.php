<?php

require_once(dirname(dirname(dirname(__FILE__)))."/lib.inc/vendor/autoload.php");

use phpseclib3\Net\SFTP;

$sftp = new SFTP('localhost', 22, 30);
$sftp->login('root', 'pass');

echo nl2br($sftp->exec('cd /var/www/trial;date'));

exit();
include_once dirname(dirname(dirname(__FILE__)))."/lib.inc/auth.php";
$microtime = microtime();
$arr = explode(' ', $microtime);
$microtime1 = $arr[0]+time();
if(isset($_SERVER['HTTP_X_COMMAND_FOR_SERVER']))
{
	$command = $_SERVER['HTTP_X_COMMAND_FOR_SERVER'];//Command-For-Server
	if($command == 'Set-Server-Time')
	{
		$localtime1 = @$_SERVER['HTTP_X_LOCAL_TIME'];
		$localtime2 = @$_POST['localtime'];
		
		$token1 = @$_SESSION['set_time_token'];
		$token2 = @$_SERVER['HTTP_X_SET_TIME_TOKEN']; // Set-Time-Token
		
		$year = date('Y');
		
		// validation
		if($localtime1 == $localtime2 /*&& $token1 == $token2*/ && $year < 2017)
		{
			
		}
	}
}
