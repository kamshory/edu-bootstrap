<?php
if(!isset($cfg))
{
	$cfg = new \stdClass;
}
$cfg->image_not_exported = array('latex.codecogs.com');
$cfg->audio_not_exported = array();





$arr_files = array();




/*
Old code
function getNumberingType($s1, $s2)
{
	$a1 = explode(".", $s1);
	$q1 = $a1[0];
	$a2 = explode(".", $s2);
	$q2 = $a2[0];

	$ret = false;

	if ($q1 == 'A' && $q2 == 'B') {
		$ret = 'upper-alpha';
	} else if ($q1 == 'a' && $q2 == 'b') {
		$ret = 'lower-alpha';
	} else if ($q1 == 'I' && $q2 == 'II') {
		$ret = 'upper-roman';
	} else if ($q1 == 'i' && $q2 == 'ii') {
		$ret = 'lower-roman';
	} else if ($q1 == '1' && $q2 == '2') {
		$ret = 'decimal';
	} else if ($q1 == '01' && $q2 == '02') {
		$ret = 'decimal-leading-zero';
	}

	return $ret;
}
*/









if(!function_exists('trimWhitespace'))
{
	function trimWhitespace($value)
	{
		return trim($value, " \r\n\t ");
	}
}
