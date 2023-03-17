<?php

namespace Pico;

class PicoConst
{

	const DATE_TIME_MYSQL = 'Y-m-d H:i:s';
	const FULL_DATE_TIME_INDONESIA_FORMAT = 'j M Y H:i';
	const SHORT_DATE_TIME_INDONESIA_FORMAT = 'j F Y H:i';
	const SELECT_OPTION_SELECTED = ' selected="selected"';
	const INPUT_CHECKBOX_CHECKED = ' checked="checked"';
	const NEW_LINE = "\r\n";
	const RAQUO = ' &raquo; ';
	const SPAN_OPEN = '<span>';
	const SPAN_CLOSE = '</span>';
	const SPAN_TITLE = '<span title="';
	const TRIM_EXTRA_SPACE = "/\s+/";
	const TRIM_NON_NUMERIC = "/[^0-9]/i";
	const PICO_EDU = "picoedu";

	const NUMBERING_TYPE = array(
		"upper-alpha"=>array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'),
		"lower-alpha"=>array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'),
		"upper-roman"=>array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'),
		"lower-roman"=>array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'),
		"decimal"=>array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10'),
		"decimal-leading-zero"=>array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10')
	);

	const TEST_STATUS_1 = 1;
	const TEST_STATUS_2 = 2;
	const TEST_STATUS_3 = 3;
	const TEST_STATUS_4 = 4;
	const TEST_STATUS_5 = 5;
	
	const TEST_STATUS = array(
		self::TEST_STATUS_1=>'Ujian',
		self::TEST_STATUS_2=>'Selesai',
		self::TEST_STATUS_3=>'Dikeluarkan',
		self::TEST_STATUS_4=>'Diblokir',
		self::TEST_STATUS_5=>'Tidak selesai'
	);
}
