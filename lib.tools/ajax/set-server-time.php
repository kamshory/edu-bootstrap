<?php
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
			include_once dirname(dirname(dirname(dirname(__FILE__))))."/server/inc-cfg.php";
			set_include_path(dirname(dirname(dirname(dirname(__FILE__))))."/server/inc/phpseclib");
			error_reporting(0);
			include_once "Net/SSH2.php";
			$ssh = new Net_SSH2($configs->ssh_host, $configs->ssh_port);
			if (!@$ssh->login($configs->ssh_user, $configs->ssh_password)) 
			{
			}
			if($localtime1 != "")
			{
				// get time zone from database
				// $configs->db_host;
				// $configs->db_user;
				// $configs->db_pass;
				// $configs->db_name;
				
				mysql_connect($configs->db_host, $configs->db_user, $configs->db_pass); 
				mysql_select_db($configs->db_name);
				
				$sql = "SELECT * FROM `config` WHERE `config_id` = 'server_time_zone' ";
				$res = mysql_query($sql);
				if(mysql_num_rows($res))
				{
					$data = mysql_fetch_assoc($res);
					$timezone = $data['value']; 
				}
				else
				{
					$tz = shell_exec('date');
					$tz = trim(preg_replace("/\s+/"," ",$tz));
					$arr = explode(' ', $tz);
					$tz = $arr[4];
					$tz = str_replace('+0', '+', $tz);
					$tz = str_replace('-0', '-', $tz);
					if($tz == '+') $tz = '-0';
					if($tz == '-') $tz = '-0';
					$timezone = 'Etc/GMT'.$tz;
				}
				date_default_timezone_set($timezone);
				$local_time = date('YmdHis', 3+strtotime($localtime1));
				$command = "/usr/sbin/rtc-pi $local_time";
				$ssh->write("$command\n");
				$output = $ssh->read('#[pP]assword[^:]*:|username@username:~\$#', NET_SSH2_READ_REGEX);
				$microtime = microtime();
				$arr = explode(' ', $microtime);
				$microtime2 = $arr[0]+time();
				$microtime3 = $microtime2 - $microtime1;
				echo "TIME HAS BEEN SYNCRHONIZED IN $microtime3 SECONDS";
			}
		}
	}
}
?>