<?php
if(!function_exists('trimWhitespace'))
{
	function trimWhitespace($value)
	{
		return trim($value, " \r\n\t ");
	}
}
