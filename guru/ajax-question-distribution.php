<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(empty(@$school_id))
{
	exit();
}

if(isset($_POST['save']) && isset($_POST['test_id']) && isset($_POST['data']))
{
	$test_id = kh_filter_input(INPUT_POST, 'test_id', FILTER_SANITIZE_STRING_NEW);
	$data = kh_filter_input(INPUT_POST, 'data');
	try
	{
		$try = @json_decode($data, true);
		if(is_array($try))
		{
			$data = json_encode($try);
			$data = addslashes($data);
			$sql = "update `edu_test` set `random_distribution` = '$data'
			where `test_id` = '$test_id' and `teacher_id` = '$teacher_id'
			";
			$database->executeUpdate($sql);
		}
	}
	catch(Exception $e)
	{
		// Do nothing
	}
}
else
{
	$test_id = kh_filter_input(INPUT_GET, 'test_id', FILTER_SANITIZE_STRING_NEW);
	if($test_id)
	{
		$randobj = array();
		$sql = "SELECT `edu_test`.`random_distribution`
		from `edu_test`
		where `edu_test`.`test_id` = '$test_id' and `edu_test`.`teacher_id` = '$teacher_id'
		";
		$stmt = $database->executeQuery($sql);
		if($stmt->rowCount() > 0)
		{
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$saveddata = $data['random_distribution'];
			$randobj = array();
			if($saveddata != '')
			{
				try
				{
					$randobj = @json_decode($saveddata, true);
				}
				catch(Exception $e)
				{
					// Do nothing
				}
			}
			$sql = "SELECT `edu_question`.`basic_competence` , count(distinct `edu_question`.`question_id`) as `colection`
			from `edu_question`
			where `edu_question`.`test_id` = '$test_id'
			group by `edu_question`.`basic_competence`
			order by `edu_question`.`basic_competence` asc
			";
			$stmt2 = $database->executeQuery($sql);
			$questions = array();
			$rows2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach($rows2 as $data)
			{
				$arr = explode('.', $data['basic_competence'], 2);		
				$number = (@$arr[0]*100 + @$arr[1]) * 1;
				if(isset($randobj['bc'.str_replace('.', '_', $data['basic_competence'])]))
				{
					$data['random'] = @$randobj['bc'.str_replace('.', '_', $data['basic_competence'])]*1;
				}
				else
				{
					$data['random'] = $data['colection']*1;
				}
				$questions[$number] = $data;
			}
			if(count($questions))
			{
				$arr_keys = array_keys($questions);
				sort($arr_keys);
				?>
				<div class="dialog-distribution">
				<form name="formrandom" id="formrandom" action="" method="get" enctype="multipart/form-data">
				<table width="100%" cellpadding="0" cellspacing="0" border="0" class="dialog-kd">
				<thead>
					<tr>
						<td width="33%">KD</td>
						<td width="33%">Ambil</td>
						<td width="33%">Dari</td>
					</tr>
				</thead>
				<tbody>
				<?php
				$total_collection = 0;
				$total_random = 0;
				foreach($arr_keys as $key)
				{
					?>
					<tr>
						<td><?php echo $questions[$key]['basic_competence'];?></td>
						<td><input type="number" class="take" data-basic-competence="<?php echo str_replace('.', '_', $questions[$key]['basic_competence']);?>" name="take_<?php echo str_replace('.', '_', $questions[$key]['basic_competence']);?>" value="<?php echo $questions[$key]['random'];?>" step="1" min="0" max="<?php echo $questions[$key]['colection'];?>"></td>
						<td><?php echo $questions[$key]['colection'];?></td>
					</tr>
					<?php
					$total_collection += $questions[$key]['colection'];
					$total_random += $questions[$key]['random'];
				}
				?>
				</tbody>
				<tfoot>
					<tr>
						<td>Total</td>
						<td><input type="number" class="take_total" step="1" min="0" value="<?php echo $total_random;?>" readonly="readonly"></td>
						<td><?php echo $total_collection;?></td>
					</tr>
				</tfoot>
				</table>
					<div class="button-area">
						<input type="hidden" name="test_id" value="<?php echo $test_id;?>" />
						<input type="button" class="com-button" id="reload-dialog" value="Muat Ulang" onclick="distribution(<?php echo $test_id;?>)">
						<input type="submit" class="com-button" id="save-dialog" value="Simpan">
						<input type="button" class="com-button" id="close-dialog" value="Batal" onclick="closeOverlayDialog()">
					</div>
				</form>    
				</div>
				<?php
			}
		}
	}
}
?>