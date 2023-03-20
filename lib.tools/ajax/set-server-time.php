<?php
require_once dirname(dirname(__DIR__)) . "/lib.inc/vendor/autoload.php";

use phpseclib3\Net\SFTP;
use phpseclib3\Exception\UnableToConnectException;

$cfg = new \stdClass;
$cfg->max_year_sync_time = 2024;
if (date('Y') < $cfg->max_year_sync_time && isset($_SERVER['HTTP_X_COMMAND_FOR_SERVER'])) {
	$command = $_SERVER['HTTP_X_COMMAND_FOR_SERVER']; //Command-For-Server
	if ($command == 'Set-Server-Time') {
		$unixtimestamp1 = @$_SERVER['HTTP_X_UNIX_TIMESTAMP'];
		$unixtimestamp2 = @$_POST['unixtimestamp'];

		// validation
		if ($unixtimestamp1 == $unixtimestamp2) {

			$unixtimestamp = round($unixtimestamp1/1000);

			try {
				$iniPath = dirname(dirname(dirname(__DIR__))) . "/sftp.ini";
				$sftpConfig = parse_ini_file($iniPath);
				$sftp = new SFTP($sftpConfig['host'], $sftpConfig['port'], $sftpConfig['timeout']);
				$sftp->login($sftpConfig['username'], $sftpConfig['password']);

				$commands = array();

				// Set date time via timedatectl
				$commands[] = "/bin/timedatectl set-ntp false";
				$commands[] = "/bin/timedatectl set-time '" . date("Y-m-d H:i:s", $unixtimestamp) . "'";

				// Set date time to rtc module via hwclock
				$commands[] = "/bin/hwclock --set --date \"" . date("m/d/Y H:i:s", $unixtimestamp) . "\"";
				$sftp->exec(implode(";", $commands));
			} catch (UnableToConnectException $e) {
				// Do nothing
			}
		}
	}
}
