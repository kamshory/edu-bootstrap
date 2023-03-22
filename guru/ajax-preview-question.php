<?php
require_once dirname(__DIR__)."/lib.inc/auth-guru.php";
if(!empty($school_id))
{
require_once dirname(__DIR__)."/lib.inc/lib.test.php";
$test_id = kh_filter_input(INPUT_POST, "test_id", FILTER_SANITIZE_STRING_NEW);
$xml_data = kh_filter_input(INPUT_POST, "text", FILTER_DEFAULT);
if(!empty($test_id) && $xml_data!= '')
{
	$picoTest = new \Pico\PicoTestCreator();
	$clear_data = $picoTest->parseRawQuestion($xml_data);

	$base_dir = dirname(__DIR__) . "/media.edu/school/$school_id/test/$test_id";
	$base_src = "media.edu/school/$school_id/test/$test_id";
	?>
    <ol class="question-ol">
    <?php
	foreach($clear_data as $question_no=>$question)
	{
		
		$object = $picoTest->parseQuestion($question);
		$isi = nl2br(\Pico\PicoDOM::filterHtml(\Pico\PicoDOM::addImages(@$object['question'], $base_dir, $base_src)));
		?>
        <li class="question-li">
        <p><?php echo $isi;?></p>
        <ol class="option-ol">
        <?php
		if(@is_array($object['option']) && count($object['option']) > 0)
		{
		foreach($object['option'] as $option_no=>$option)
		{
			?><li style="list-style-type:<?php echo $object['numbering'];?>" class="option-li">
            <span class="option-circle<?php echo $picoEdu->ifMatch($option['value'] > 0, ' option-circle-selected', '');?>"></span>
            <?php
			$isi_pilihan = nl2br(utf8ToEntities(\Pico\PicoDOM::filterHtml(\Pico\PicoDOM::addImages($option['text'], $base_dir, $base_src))));
			echo $isi_pilihan;
			?>
            </li>
            <?php
		}
		}
		?>
        </ol>
        </li>
        <?php
	}
	?>
    </ol>
    <?php
}
}
?>
