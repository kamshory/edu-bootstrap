<?php
include_once dirname(dirname(__FILE__))."/lib.inc/auth-guru.php";
if(@$school_id != 0)
{
include_once dirname(dirname(__FILE__))."/lib.inc/lib.test.php";
$test_id = kh_filter_input(INPUT_POST, 'test_id', FILTER_SANITIZE_STRING_NEW);
$xml_data = kh_filter_input(INPUT_POST, 'text', FILTER_DEFAULT);
if($test_id != 0 && $xml_data!= '')
{
	$clear_data = parseRawQuestion($xml_data);

	$base_dir = dirname(dirname(__FILE__))."/media.edu/school/$school_id/test/$test_id";
	$base_src = "media.edu/school/$school_id/test/$test_id";
	?>
    <ol class="question-ol">
    <?php
	foreach($clear_data as $question_no=>$question)
	{
		
		$object = parseQuestion($question);
		$isi = nl2br(filter_html(addImages(@$object['question'], $base_dir, $base_src)));
		?>
        <li class="question-li">
        <p><?php echo $isi;?></p>
        <ol class="option-ol">
        <?php
		if(@is_array($object['option']))
		{
		if(count($object['option']))
		{
		foreach($object['option'] as $option_no=>$option)
		{
			?><li style="list-style-type:<?php echo $object['numbering'];?>" class="option-li">
            <span class="option-circle<?php if($option['value']) echo ' option-circle-selected';?>"></span>
            <?php
			$isi_pilihan = nl2br(UTF8ToEntities(filter_html(addImages($option['text'], $base_dir, $base_src))));
			echo $isi_pilihan;
			?>
            </li>
            <?php
		}
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
