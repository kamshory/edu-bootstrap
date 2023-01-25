<?php
function parseSQL($sql_text) 
{
	$arr = explode("\n", $sql_text);
	foreach($arr as $key=>$val)
	{
		$arr[$key] = trim($val, "\r");
	}
	$append = 0;
	$skip = 0;
	$start = 1;
	$nquery = -1;
	$delimiter = ";";
	$query_array = array();
	$delimiter_array = array();
	
	foreach($arr as $line=>$text)
	{
		if($text == "")
		{
			if($append == 1)
			{
				$query_array[$nquery] .= "\r\n";
			}
		}
		if($append == 0)
		{
			if(stripos(ltrim($text, " \t "), "--") === 0)
			{
				$skip = 1;
				$nquery++;
				$start = 1;
				$append = 0;
			}
			else
			{
				$skip = 0;
			}
		}
		if($skip == 0)
		{
			if($start == 1)
			{
				$nquery++;
				$query_array[$nquery] = "";
				$delimiter_array[$nquery] = $delimiter;
				$start = 0;
			}
			$query_array[$nquery] .= $text."\r\n";
			$delimiter_array[$nquery] = $delimiter;
			$text = ltrim($text, " \t ");
			$start = strlen($text)-strlen($delimiter)-1;
			if(stripos(substr($text, $start), $delimiter) !== false || $text == $delimiter)
			{
				$nquery++;
				$start = 1;
				$append = 0;
			}
			else
			{
				$start = 0;
				$append = 1;
			}
			$delimiter_array[$nquery] = $delimiter;
			if(stripos($text, "delimiter ") !== false)
			{
				$text = trim(preg_replace("/\s+/"," ",$text));
				$arr2 = explode(" ", $text);
				$delimiter = $arr2[1];
				$nquery++;
				$delimiter_array[$nquery] = $delimiter;
				$start = 1;
				$append = 0;
			}
		}
	}
	$result = array();
	foreach($query_array as $line=>$sql)
	{
		$delimiter = $delimiter_array[$line];
		if(stripos($sql, "delimiter ") === 0)
		{
		}
		else
		{
			$sql = rtrim($sql, " \r\n\t ");
			$sql = substr($sql, 0, strlen($sql)-strlen($delimiter));			
			$result[] = array("query"=> $sql, "delimiter"=>$delimiter);
		}
	}
	return $result;
}

function parseHeaders($input, $header = null)
{
	$headers = explode("\r\n", $input);
    $output = array();
    if('HTTP' === substr($headers[0], 0, 4))
	{
        list(, $output['status'], $output['status_text']) = explode(' ', $headers[0]);
        unset($headers[0]);
    }
    foreach ($headers as $v) {
        $h = preg_split('/:\s*/', $v);
        $output[($h[0])] = $h[1];
    }
    if(null !== $header) 
	{
        if (isset($output[($header)]))
		{
            return $output[($header)];
        }
        return;
    }
    return $output;
}
$error_message = "";

include_once dirname(dirname(__FILE__))."/lib.inc/auth-admin.php";
$cfg->repo_domain = "http://repo.edu.planetbiru.com";
$program_version = trim($picoEdu->getApplicationVersion());
$cfg->module_title = "Update Program";
if(isset($_POST['download']) && isset($_POST['version']))
{
	$version = trim(@$_POST['version']);
	$username = "superplanetedu";
	$password = "5mr73duc4710n0k37";
	$_SESSION['version'] = $version;
	// download database begin
	$filename = "database.zip";
	$target_url = $cfg->repo_domain."/update/edu/$version/$program_version/$filename";
	$filename = dirname(dirname(__FILE__))."/74594b6204df3e1683455de22c68aa22.zip";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "PHP-CURL-SIAS-INSTALLER");
	curl_setopt($ch, CURLOPT_HTTPHEADER, 
		array(
			"Server: ".$_SERVER['SERVER_NAME'],
			"Username: ".$username,
			"Password: ".$password
			)
		);
	$server_output = curl_exec($ch);
	file_put_contents($filename, $server_output);
	// download database end	

	// download program begin
	$filename = "update.zip";
	$target_url = $cfg->repo_domain."/update/edu/$version/$program_version/$filename";
	$filename = dirname(dirname(__FILE__))."/ff8e3dd3964f6864ec4dd6e5a796754a.zip";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $target_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "PHP-CURL-SIAS-INSTALLER");
	curl_setopt($ch, CURLOPT_HTTPHEADER, 
		array(
			"Server: ".$_SERVER['SERVER_NAME'],
			"Username: ".$username,
			"Password: ".$password
			)
		);
	$server_output = curl_exec($ch);
	file_put_contents($filename, $server_output);
	// download program end	
	
	header("Location: ".basename($_SERVER['PHP_SELF'])."?step=2");
}
if(isset($_POST['extract']))
{
	$oke = 1;
	$zip = new ZipArchive;
	$filename = dirname(dirname(__FILE__))."/ff8e3dd3964f6864ec4dd6e5a796754a.zip";
	if(file_exists($filename))
	{
		if(filesize($filename) > 0)
		{
			if ($zip->open($filename) === TRUE) 
			{
				$zip->extractTo(dirname(dirname(__FILE__))."/");
				$zip->close();
				$oke = $oke * 1;
			} 
			else 
			{
				$oke = $oke * 0;
			}
		}
		@unlink($filename);
	}
	$filename = dirname(dirname(__FILE__))."/74594b6204df3e1683455de22c68aa22.zip";
	if(file_exists($filename))
	{
		if(filesize($filename) > 0)
		{
			if($zip->open($filename) === TRUE) 
			{
				$zip->extractTo(dirname(dirname(__FILE__))."/");
				$zip->close();
				$path = dirname(dirname(__FILE__))."/database.sql";
				// extract file
				if(file_exists($path))
				{
					// parse and execute query
					$sql = file_get_contents($path);
					$query = parseSQL($sql);
					$oke2 = 1;
					
					foreach($query as $key=>$val)
					{
						$sql = $val['query'];
						$delimiter = $val['delimiter'];
						$stmt = $database->executeUpdate($sql);
						if($stmt->rowCount() == 0)
						{
							$oke2 = $oke2 * 0;
						}
					}
					@unlink($path);
				}
			} 
			else 
			{
				$oke = $oke * 0;
			}
		}
		@unlink($filename);
	}
	if($oke)
	{
		$version = addslashes($_SESSION['version']);
		$username = "superplanetedu";
		$password = "5mr73duc4710n0k37";
		$_SESSION['version'] = $version;
		// download database begin
		$filename = "database.zip";
		$target_url = $cfg->repo_domain."/changelog/edu/$version";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $target_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "PHP-CURL-SIAS-INSTALLER");
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			array(
				"Server: ".$_SERVER['SERVER_NAME'],
				"Username: ".$username,
				"Password: ".$password
				)
			);
		$server_output = curl_exec($ch);
		$json = ($server_output);
		$object = json_decode($json, true);
		$change_log = $object['change_log'];
		$version_id = addslashes($object['version_code']);
		$name = addslashes($object['name']);
		$time_release = addslashes($object['release_time']);
		$time_update = $picoEdu->getLocalDateTime();
		$change_log = addslashes($change_log);
		
		$sql = "start transaction;";
		$database->executeTransaction($sql);
		
		$sql = "REPLACE INTO `version` 
		(`version_id`, `name`, `time_release`, `time_update`, `change_log`, `current_version`, `active`) VALUES
		('$version_id', '$name', '$time_release', '$time_update', '$change_log', 0, 1);
		";
		$database->executeReplace($sql);
		
		$sql = "UPDATE `version` set `current_version` = 0;";
		$database->executeUpdate($sql);
		$sql = "UPDATE `version` set `current_version` = 1 where `version_id` = '$version_id';";
		$database->executeUpdate($sql);
		
		$sql = "commit;";
		$database->executeTransaction($sql);
	}
	
	header("Location: ".basename($_SERVER['PHP_SELF'])."?step=3");
}

$step = 0;
if(!isset($_GET['step']))
{
	$step = 0;
}
else
{
	$step = trim($_GET['step']) * 1;
}
if($step == 1)
{
include_once dirname(__FILE__)."/lib.inc/header.php";
$target_url = $cfg->repo_domain."/upgrade/edu/$program_version";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "PHP-CURL-SIAS-INSTALLER");
curl_setopt($ch, CURLOPT_HTTPHEADER, 
	array(
		"Server: ".$_SERVER['SERVER_NAME']
		)
	);
$server_output = curl_exec($ch);
if($server_output === false)
{
	?>
	<h3>Tahap 1 - Mendownload Aplikasi</h3>
	<div class="warning">Tidak bisa terhubung ke server repositori Planet Edu. Pastikan bahwa server Planet Edu terhubung ke internet. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali</a>.</div>
	<?php
}
else
{
$version_list = array();
if($server_output != '')
{
	$version_list = json_decode($server_output, true);
}
if(isset($version_list))
{
	if(is_array($version_list))
	{
		if(count($version_list) > 0)
		{
?>
<script type="text/javascript">
var version_list = [];
<?php 
if($version_list)
echo "version_list = ".$server_output.";";?>

$(document).on('click', '#changelog', function(e){
	var ver = $('#version').val();
	var message = getChangeLog(ver);
	$('.dialog-log-inner').html(message);
	$('.dialog-log').dialog({
		modal:true,
		title:'Catatan Perubahan',
		width:600,
		height:400
	});
});
function getChangeLog(version)
{
	var i, j, changeLog = '';
	for(i in version_list)
	{
		j = version_list[i];
		if(j.version == version)
		{
			changeLog = j.change_log || '';
			break;
		}
	}
	return changeLog;
}
</script>
<div class="dialogs" style="display:none">
	<div class="dialog-log" title="Catatan Perubahan">
    	<div class="dialog-log-inner">
        </div>
    </div>
</div>
	<h3>Tahap 1 - Mendownload Aplikasi</h3>
    <form name="downloadform" id="downloadform" action="" method="post" enctype="multipart/form-data">
	<div class="info">Saat ini Anda menggunakan <strong>versi <?php echo $program_version;?></strong>. Untuk mengupdate aplikasi, silakan download versi aplikasi yang akan digunakan.</div>
        <div class="button-area">
        	Update Ke 
            <select name="version" id="version" required="required">
            <?php
			foreach($version_list as $key=>$val)
			{
				?>
				<option value="<?php echo $val['version'];?>"><?php echo $val['name'];?><?php echo ($val['stable'])?' (Stabil)':'';?></option>
				<?php
			}
			?>
            </select> 
            <input type="submit" name="download" id="download" value="Lanjutkan" />
            <input type="button" id="changelog" value="Lihat Perubahan" />
            <input type="button" id="back" value="Kembali" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?step=<?php echo ($step-1);?>'" />
        </div>
    </form>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table">
	<thead>
	  <tr>
	    <td width="25">No</td>
	    <td>Versi</td>
	    <td>Waktu Rilis</td>
	    <td>Ukuran Program</td>
	    <td>MD5 Program</td>
	    <td>Ukuran Database</td>
	    <td>MD5 Database</td>
	    <td>Stabil</td>
      </tr>
    </thead>
    <tbody>  
<?php
$no = 0;
if(isset($version_list))
{
	if(is_array($version_list))
	{
		foreach($version_list as $key=>$val)
		{
			$no++;
			$class = (@$val['version']==$program_version)?"data-default":"";
		?>
	  <tr class="<?php echo $class;?>">
	    <td align="right"><?php echo $no;?></td>
	    <td><?php echo $val['version'];?></td>
	    <td><?php echo translateDate(date('j F Y H:i:s', strtotime($val['time'])));?></td>
	    <td><?php echo number_format($val['program_size'], 0, ',', '.');?> byte</td>
	    <td><span title="<?php echo $val['program_md5'];?>"><?php echo substr($val['program_md5'], 0, 12);?>&hellip;</span></td>
	    <td><?php echo number_format($val['database_size'], 0, ',', '.');?> byte</td>
	    <td><span title="<?php echo $val['database_md5'];?>"><?php echo substr($val['database_md5'], 0, 12);?>&hellip;</span></td>
	    <td><?php echo ($val['stable'])?'Ya':'Tidak';?></td>
      </tr>
			<?php
        }
    }
}
?>
</tbody>
</table>
        

<?php
	}
else
{
?>
	<h3>Tahap 1 - Mendownload Aplikasi</h3>
	<div class="warning">Update aplikasi tidak tersedia. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>">Klik di sini untuk kembali</a></div>
<?php
}
}
}
}
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else if($step == 2)
{
	include_once dirname(__FILE__)."/lib.inc/header.php";
	if(file_exists(dirname(dirname(__FILE__))."/ff8e3dd3964f6864ec4dd6e5a796754a.zip") 
	|| file_exists(dirname(dirname(__FILE__))."/74594b6204df3e1683455de22c68aa22.zip"))
	{
	?>
	<h3>Tahap 2 - Mengekstrak Program</h3>
	<div class="info">File update aplikasi telah berhasil didownload. Silakan ekstrak file tersebut.</div>
    <form name="extractform" id="extractform" action="" method="post" enctype="multipart/form-data">
        <div class="button-area">
      <input type="submit" name="extract" id="extract" value="Lanjutkan" />
      <input type="button" id="back" value="Kembali" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?step=<?php echo ($step-1);?>'" />
    	</div>
    </form>
    <?php
	}
	else
	{
	?>
	<h3>Tahap 2 - Mengekstrak Program</h3>
	<div class="warning">Sepertinya proses mendownload file update aplikasi dari repositori gagal dilakukan. Silakan ulangi lagi. <a href="<?php echo basename($_SERVER['PHP_SELF']);?>?step=<?php echo ($step-1);?>">Klik di sini untuk mendownload kembali</a>.</div>
	<?php
	}
	include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else if($step == 3)
{
include_once dirname(__FILE__)."/lib.inc/header.php";
?>
<h3>Tahap 3 - Update Selesai</h3>
<div class="info">Program berhasil diperbarui.</div>
<div class="button-area">
  <input type="button" id="chengelog" value="Versi Program" onclick="window.location='version.php'" />
  <input type="button" id="next" value="Selesai" onclick="window.location='index.php'" />
</div>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
else
{
include_once dirname(__FILE__)."/lib.inc/header.php";

?>
<h3>Update Aplikasi</h3>
<p>Update aplikasi diperlukan untuk menambahkan fitur aplikasi agar dapat meyesuaikan dengan kebutuhan dan juga untuk meningkatkan kualitas dan stabilitas sistem. </p>
<div class="button-area">
  <input type="button" id="next" value="Lanjutkan" onclick="window.location='<?php echo basename($_SERVER['PHP_SELF']);?>?step=<?php echo ($step+1);?>'" />
</div>
<?php
include_once dirname(__FILE__)."/lib.inc/footer.php";
}
?>