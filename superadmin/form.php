<?php
//define('DB_HOST', 'localhost');
//define('DB_NAME', 'iffxwgwj_sia');
//define('DB_USER', 'iffxwgwj_sia');
//define('DB_PASSWORD', '94r0n9g4nt3n9');
//define('DB_PREFIX', '');

define("DB_HOST", "localhost");
define("DB_NAME", "mini_edu");
define("DB_USER", "root");
define("DB_PASSWORD", "");
define("DB_PREFIX", "");

$lang_pack = array();
$lang_pack['en']['button_add'] = "Add";
$lang_pack['en']['button_delete'] = "Delete";
$lang_pack['en']['button_edit'] = "Edit";
$lang_pack['en']['button_save'] = "Save";
$lang_pack['en']['button_show_all'] = "Show All";
$lang_pack['en']['button_search'] = "Search";
$lang_pack['en']['button_create_new'] = "Create New";
$lang_pack['en']['button_activate'] = "Activate";
$lang_pack['en']['button_deactivate'] = "Deactivate";
$lang_pack['en']['message_confirm_delete_record'] = "Are you sure you want to delete selected record?";
$lang_pack['en']['message_search_no_result'] = "Searching has no result. Please try again with other keyword.";
$lang_pack['en']['message_search_data_not_found'] = "Data not found.";
$lang_pack['en']['message_search_link_add_new'] = "Click here to add new.";
$lang_pack['en']['message_search_link_back'] = "Click here to back.";
$lang_pack['en']['label_yyyy_mm_dd'] = "YYYY-MM-DD";
$lang_pack['en']['label_yyyy_mm_dd_hh_ii_ss'] = "YYYY-MM-DD HH:II:SS";
$lang_pack['en']['label_hh_ii_ss'] = "HH:II:SS";
$lang_pack['en']['label_row'] = "Row";
$lang_pack['en']['label_to'] = "to";
$lang_pack['en']['label_from'] = "from";
$lang_pack['en']['label_yes'] = "Yes";
$lang_pack['en']['label_no'] = "No";
$lang_pack['en']['tip_needed'] = "Needed";

$lang_pack['id']['button_add'] = "Tambah";
$lang_pack['id']['button_delete'] = "Hapus";
$lang_pack['id']['button_edit'] = "Ubah";
$lang_pack['id']['button_save'] = "Simpan";
$lang_pack['id']['button_show_all'] = "Tampilkan Semua";
$lang_pack['id']['button_search'] = "Cari";
$lang_pack['id']['button_create_new'] = "Buat Baru";
$lang_pack['id']['button_activate'] = "Aktifkan";
$lang_pack['id']['button_deactivate'] = "Nonaktifkan";
$lang_pack['id']['message_confirm_delete_record'] = "Apakah Anda yakin akan menghapus baris yang dipilih?";
$lang_pack['id']['message_search_no_result'] = "Pencarian tidak menemukan hasil. Silakan ulangi dengan kata kunci yang lain.";
$lang_pack['id']['message_search_data_not_found'] = "Data tidak ditemukan.";
$lang_pack['id']['message_search_link_add_new'] = "Klik di sini untuk membuat baru.";
$lang_pack['id']['message_search_link_back'] = "Klik di sini untuk kembali.";
$lang_pack['id']['label_yyyy_mm_dd'] = "TTTT-BB-HH";
$lang_pack['id']['label_yyyy_mm_dd_hh_ii_ss'] = "TTTT-BB-HH JJ:MM:DD";
$lang_pack['id']['label_hh_ii_ss'] = "JJ:MM:DD";
$lang_pack['id']['label_row'] = "Baris";
$lang_pack['id']['label_to'] = "hingga";
$lang_pack['id']['label_from'] = "dari";
$lang_pack['id']['label_yes'] = "Ya";
$lang_pack['id']['label_no'] = "Tidak";
$lang_pack['id']['tip_needed'] = "Wajib";

$cfgdb = new StdClass();
$cfgdb->connection = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);

if(!$cfgdb->connection)
{
exit();
}

$cfgdb->selecteddb = @mysql_select_db(DB_NAME, $cfgdb->connection);
if(!$cfgdb->selecteddb)
{
exit();
}

function createEnumOption($database, $table, $field, $defaultValue=null)
{
	$sql = "show columns from `$table` where `Field` like '$field' ";
	$stmt = $database->executeQuery($sql);
	if ($stmt->rowCount() > 0) {
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$enum = $data['Type'];
		$off = strpos($enum, "(");
		$enum = substr($enum, $off + 1, strlen($enum) - $off - 2);
		$values = explode(",", $enum);

		for ($n = 0; $n < Count($values); $n++) {
			$val = substr($values[$n], 1, strlen($values[$n]) - 2);
			$val = str_replace("''", "'", $val);
			$values[$n] = array($val, $val);
		}
		return $values;
	}
	return $defaultValue;
}

function createFilter($type)
{
$datatype = $type;
$p = stripos($datatype, "(");
if($p !== false)
{
	$datatype = trim(substr($datatype, 0, $p));
}
$dt = strtoupper($datatype);

$array = array(
"FILTER_SANITIZE_NUMBER_INT",
"FILTER_SANITIZE_NUMBER_UINT",
"FILTER_SANITIZE_NUMBER_OCTAL",
"FILTER_SANITIZE_NUMBER_HEXADECIMAL",
"FILTER_SANITIZE_NUMBER_FLOAT",
"FILTER_SANITIZE_STRING_NEW",
"FILTER_SANITIZE_STRING_NEW_INLINE",
"FILTER_SANITIZE_NO_DOUBLE_SPACE",
"FILTER_SANITIZE_STRIPPED",
"FILTER_SANITIZE_SPECIAL_CHARS",
"FILTER_SANITIZE_ALPHA",
"FILTER_SANITIZE_ALPHANUMERIC",
"FILTER_SANITIZE_ALPHANUMERICPUNC",
"FILTER_SANITIZE_STRING_NEW_BASE64",
"FILTER_SANITIZE_EMAIL",
"FILTER_SANITIZE_URL",
"FILTER_SANITIZE_IP",
"FILTER_SANITIZE_ENCODED",
"FILTER_SANITIZE_COLOR",
"FILTER_SANITIZE_MAGIC_QUOTES",
"FILTER_SANITIZE_PASSWORD");

$opt = '';
$sel = '';

switch($dt)
{
case 'TINYINT':
case 'SMALLINT':
case 'MEDIUMINT':
case 'INT':
case 'BIGINT':
$st = 'FILTER_SANITIZE_NUMBER_INT';
break;
case 'DECIMAL':
case 'FLOAT':
case 'DOUBLE':
case 'REAL':
$st = 'FILTER_SANITIZE_NUMBER_FLOAT';
break;
case 'BIT':
case 'BOOLEAN':
case 'SERIAL':
$st = 'FILTER_SANITIZE_NUMBER_UINT';
break;
case 'DATE':
case 'DATETIME':
case 'TIMESTAMP':
case 'TIME':
case 'YEAR':
$st = 'FILTER_SANITIZE_STRING_NEW';
break;
case 'CHAR':
case 'VARCHAR':
$st = 'FILTER_SANITIZE_SPECIAL_CHARS';
break;
case 'TINYTEXT':
case 'TEXT':
case 'MEDIUMTEXT':
case 'LONGTEXT':
$st = 'FILTER_SANITIZE_SPECIAL_CHARS';
break;
case 'BINARY':
case 'VARBINARY':
$st = 'FILTER_SANITIZE_SPECIAL_CHARS';
break;
case 'TINYBLOB':
case 'MEDIUMBLOB':
case 'BLOB':
case 'LONGBLOB':
$st = 'FILTER_SANITIZE_SPECIAL_CHARS';
break;
case 'ENUM':
case 'SET':
$st = 'FILTER_SANITIZE_SPECIAL_CHARS';
break;
}

foreach($array as $k=>$v)
{
	if($v == $st)
	{
		$sel = ' selected="selected"';
	}
	else
	{
		$sel = '';
	}
	$v2 = substr($v, 16);
	$opt .= "<option value=\"$v\"$sel>$v2</option>\r\n";
}

return $opt;

}

function filterDataType($type, $field='')
{
$datatype = $type;
$p = stripos($datatype, "(");
if($p !== false)
{
	$datatype = trim(substr($datatype, 0, $p));
}
$dt = strtoupper($datatype);
switch($dt)
{
case 'TINYINT':
case 'SMALLINT':
case 'MEDIUMINT':
case 'INT':
case 'BIGINT':
$st = 'int';
break;
case 'DECIMAL':
case 'FLOAT':
case 'DOUBLE':
case 'REAL':
$st = 'float';
break;
case 'BIT':
case 'BOOLEAN':
case 'SERIAL':
$st = 'uint';
break;
case 'DATE':
$st = 'date';
break;
case 'TIME':
$st = 'time';
break;
case 'DATETIME':
$st = 'datetime';
break;
case 'TIMESTAMP':
$st = 'datetime';
break;
case 'YEAR':
$st = 'int';
break;
case 'CHAR':
case 'VARCHAR':
$st = 'text';
break;
case 'TINYTEXT':
case 'TEXT':
case 'MEDIUMTEXT':
case 'LONGTEXT':
$st = 'text';
break;
case 'BINARY':
case 'VARBINARY':
$st = 'text';
break;
case 'TINYBLOB':
case 'MEDIUMBLOB':
case 'BLOB':
case 'LONGBLOB':
$st = 'text';
break;
case 'ENUM':
case 'SET':
$st = 'text';
break;
}
if($field == 'email')
{
	$st = 'email';
}
if($field == 'password')
{
	$st = 'password';
}

$array = array(
'text',
'email',
'password',
'int',
'float',
'date',
'time',
'datetime',
'color'
);
$opt = '';
foreach($array as $k=>$v)
{
	if($v == $st)
	{
		$sel = ' selected="selected"';
	}
	else
	{
		$sel = '';
	}
	$opt .= "<option value=\"$v\"$sel>$v</option>\r\n";
}

return $opt;

}

function parseQueryString($str) { 
    $op = array(); 
    $pairs = explode("&", $str); 
    foreach ($pairs as $pair) { 
        list($k, $v) = array_map("urldecode", explode("=", $pair)); 
        $op[$k] = $v; 
    } 
    return $op; 
} 

function parse_query($str){
    // Separate all name-value pairs
    $pairs = explode('&', $str); 
    foreach($pairs as $pair) {
         
        // Pull out the names and the values
         list($name, $value) = explode('=', $pair, 2);
         
        // Decode the variable name and look for arrays
         list($name, $index) = explode('[][]', urldecode($name));
         
        // Arrays
         if(isset($index)) {
             
            // Declare or add to the global array defined by $name
             global $$name;
             if(!isset($$name)) $$name = array();
             
            // Associative array
             if($index != "") {
                 ${$name}[$index] = addslashes(urldecode($value));
                 
            // Ordered array
             } else {
                 array_push($$name, addslashes(urldecode($value)));
             }
         
        // Variables
         } else {
             
            // Declare or overwrite the global variable defined by $name
             global $name;
             $$name = addslashes(urldecode($value));
         }
     }
 }
 
if(isset($_POST['data2send']))
{
	@parse_str($_POST['data2send'], $arr);
	$_POST = $arr;
}

$table = trim(@$_POST['table']);
$field = @$_POST['field'];
$lang = @$_POST['lang'];

$needed = array();

if(!$lang) $lang = 'en';
$lpack = $lang_pack[$lang];

$module_title = ucwords(str_replace(array("-", "_"), " ", $table));

$str = "";

if(isset($field))
{
$str .= "<"."?php
include_once dirname(dirname(dirname(__FILE__))).\"/planetbiru/lib.inc/auth.php\";

\$cfg->module_title = \"$module_title\";
include_once dirname(dirname(__FILE__)).\"/lib.inc/cfg.pagination.php\";
";

$str .= "if(count(@\$_POST))
{
";

$field_new = array();
$field_edit = array();
$field_detail = array();
$field_row = array();
$field_key = array();
$field_normalization = array();
$field_normalization_row = array();
$hk = 0;
foreach($field as $k=>$field)
{
	if(
	   isset($_POST['include_new_'.$field]) ||
	   isset($_POST['include_edit_'.$field]) ||
	   isset($_POST['include_key_'.$field]) ||
	   isset($_POST['include_row_'.$field])
	   )
	{
		$filter = @$_POST['filter_'.$field];
		if(strlen($filter))
		$filter = ", $filter";
		$str .= "\t\$$field = kh_filter_input(INPUT_POST, '$field'$filter);\r\n";
		if(isset($_POST['include_key_'.$field]) && $hk == 0)
		{
			$hk++;
			$str .= "\t\$$field"."2 = kh_filter_input(INPUT_POST, '$field"."2'$filter);\r\n";
			$str .= "\tif(!isset(\$_POST['$field']))\r\n";
			$str .= "\t{\r\n\t\t$$field = \$$field"."2;\r\n\t}\r\n";
		}
	}
	if(isset($_POST['include_new_'.$field]))
	$field_new[] = $field;
	if(isset($_POST['include_edit_'.$field]))
	$field_edit[] = $field;
	if(isset($_POST['include_detail_'.$field]))
	$field_detail[] = $field;
	if(isset($_POST['include_row_'.$field]))
	$field_row[] = $field;
	if(isset($_POST['include_key_'.$field]))
	{
	$field_key[] = $field;
	$field_key_type[] = @$_POST['filter_'.$field];
	}
	
	if(@$_POST['inputtype_'.$field] == 'menu')
	{
		$field_normalization[] = $field;
		if(isset($_POST['include_row_'.$field]))
		{
			$field_normalization_row[] = $field;
		}
	}			   
	
	if(isset($_POST['needed_'.$field]))
	$needed[] = $field;
}

$str .= "}
";

if(count($field_key))
{
$edit_key = $field_key[0];
$edit_key_type = $field_key_type[0];
}
else
{
$edit_key = '';
$edit_key_type = 'FILTER_SANITIZE_DEFAULT';
}


// POST
$str .= "
if(isset(\$_POST['set_active']) && isset(\$_POST['$edit_key']))
{
\t$picoEdu->changerecordstatus('active', \$_POST['$edit_key'], DB_PREFIX.\"$table\", '$edit_key', 1);
}
if(isset(\$_POST['set_inactive']) && isset(\$_POST['$edit_key']))
{
\t$picoEdu->changerecordstatus('active', \$_POST['$edit_key'], DB_PREFIX.\"$table\", '$edit_key', 0);
}
if(isset(\$_POST['delete']) && isset(\$_POST['$edit_key']))
{
\tdeleterecord(\$_POST['$edit_key'], DB_PREFIX.\"$table\", '$edit_key');
}


";


// SQL INSERT
$str .= "if(isset(\$_POST"."['save']".") && @\$_GET"."['option']"."=='add')
{
";

if(count($field_key))
{
$edit_key = $field_key[0];
$edit_key_type = $field_key_type[0];
}
else
{
$edit_key = '';
$edit_key_type = 'FILTER_SANITIZE_DEFAULT';
}


$str .= "\t\$sql = \"INSERT INTO `\".DB_PREFIX.\"$table` 
\t(`".implode("`, `", $field_new)."`) values
\t('$".implode("', '$", $field_new)."')\";
";
$str .= "\tmysql_query(\$sql);
\t\$sql = \"select last_insert_id()\";
\t\$res = mysql_query(\$sql);
\t\$dt = mysql_fetch_row(\$res);
\t\$id = \$dt[0];
\tif(\$id == 0)
\t{
\t\t\$id = kh_filter_input(INPUT_POST, \"$edit_key\", $edit_key_type);
\t}
\theader(\"Location:\".basename(\$_SERVER"."['PHP_SELF']".").\"?option=detail&$edit_key=\$id\");
}
";

// SQL UPDATE
$str .= "if(isset(\$_POST"."['save']".") && @\$_GET"."['option']"."=='edit')
{
";
$str .= "\t\$sql = \"update `\".DB_PREFIX.\"$table` set 
\t";
foreach($field_edit as $k=>$field2)
{
	$str .= "`$field2` = '\$$field2', ";
}
$str = rtrim($str, " , ");
$str .= "
\twhere `$edit_key` = '$$edit_key"."2'\";\r\n";
$str .= "\tmysql_query(\$sql);
\theader(\"Location:\".basename(\$_SERVER"."['PHP_SELF']".").\"?option=detail&$edit_key=$$edit_key\");
}
";
$str .= "if(@\$_GET"."['option']"."=='add')
{
";
$str .= "include_once dirname(__FILE__).\"/lib.inc/header.php\";
";
$str .= "?".">
";
$str .= "<form name=\"form$table\" id=\"form$table\" action=\"\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"return checkForm(this, '".$lpack['tip_needed']."')\">
";
// FORM
$str .= "\t<table width=\"800\" border=\"0\" class=\"two-side-table\" cellspacing=\"0\" cellpadding=\"0\">
";
foreach($field_new as $k=>$field2)
{
	$caption = @$_POST['caption_'.$field2];
	if($k==0) $width = " width=\"200\"";
	else $width = "";
	$str .= "\t\t<tr>\r\n\t\t<td$width>".$caption."</td>\r\n\t\t<td>"; 
	$nd = (in_array($field2, $needed))?" data-required=\"true\"":"";

	switch($_POST['inputtype_'.$field2])
	{
		case 'text':
		$type2 = 'text';
		$attr = '';
		switch($_POST['data_type_'.$field2])
		{
			case 'date':
			$class = ' input-text-date';
			$label = ' '.$lpack['label_yyyy_mm_dd'];
			break;
			case 'time':
			$class = ' input-text-time';
			$label = ' '.$lpack['label_hh_ii_ss'];
			break;
			case 'datetime':
			$class = ' input-text-datetime';
			$label = ' '.$lpack['label_yyyy_mm_dd_hh_ii_ss'];
			break;
			case 'text':
			$class = ' input-text-long';
			$label = '';
			break;
			case 'email':
			$class = ' input-text-long';
			$label = '';
			$attr = 'data-type="email" ';
			break;
			case 'password':
			$class = ' input-text-long';
			$label = '';
			$type2 = 'password';
			break;
			case 'int':
			$class = ' input-text-medium';
			$label = '';
			$type2 = 'number';
			break;
			case 'float':
			$class = ' input-text-medium';
			$label = '';
			$type2 = 'number" step="any';
			break;
			default:
			$class = '';
			$label = '';
			$type2 = 'text';
			break;
		}
		$str .= "<input type=\"$type2\" class=\"input-text$class\" name=\"$field2\" id=\"$field2\"$nd autocomplete=\"off\" $attr/>$label";
		break;
		case 'textarea':
		$str .= "<textarea class=\"input-textarea\" name=\"$field2\" id=\"$field2\"$nd></textarea>";
		break;
		case 'menu':
		$table_name = $field2;
		$idp = stripos($table_name, "_id");
		if($idp === strlen($table_name)-3)
		{
			$table_name = substr($table_name, 0, strlen($table_name)-3);
		}
		$str .= "<select class=\"input-select\" name=\"$field2\" id=\"$field2\"$nd>
		<option value=\"\"></option>
		<"."?php echo selectoption('$table_name', '$field2'); ?".">\r\n\t\t</select>";
		break;
		case 'checkbox10':
		$str .= "<label><input type=\"checkbox\" class=\"input-checkbox\" name=\"$field2\" value=\"1\" id=\"$field2\"$nd> ".ucwords(strtolower(str_replace("_"," ",$field2)))."</label>
		";
		break;
		case 'enum':
		$tab = addslashes($_POST['table']);
		$opt = createEnumOption($tab, $field2);
		
		$str .= "<select class=\"input-select\" name=\"$field2\" id=\"$field2\"$nd>
		<option value=\"\"></option>\r\n";
		$ret = "";
		foreach($opt as $k=>$v)
		{
			$ret .= "\t\t<option value=\"".$v[1]."\">".$v[0]."</option>\r\n";
		}
		$str .= $ret;
		$str .= "\t\t</select>";
		break;
	}
	$str .="</td>\r\n\t\t</tr>\r\n";			  
}
$str .= "\t\t<tr>\r\n\t\t<td>&nbsp;</td>\r\n\t\t<td><input type=\"submit\" name=\"save\" id=\"save\" class=\"com-button\" value=\"".$lpack['button_save']."\" /> <input type=\"button\" name=\"showall\" id=\"showall\" value=\"".$lpack['button_show_all']."\" class=\"com-button\" onclick=\"window.location='<?php echo basename(\$_SERVER['PHP_SELF']);?>'\" /></td>\r\n\t\t</tr>\r\n";
$str .= "\t</table>
";
$str .= "</form>
";
$str .= "<"."?php getDefaultValues(DB_PREFIX.'$table', array('".implode("','", $field_new)."')); ?".">\r\n";
$str .= "<"."?php
include_once dirname(__FILE__).\"/lib.inc/footer.php\";
";
$str .= "
}
";
$str .= "else if(@\$_GET"."['option']"."=='edit')
{
";
$str .= "include_once dirname(__FILE__).\"/lib.inc/header.php\";
\$edit_key = kh_filter_input(INPUT_GET, '$edit_key', $edit_key_type);
\$sql = \"select `\".DB_PREFIX.\"$table"."`.* 
from `\".DB_PREFIX.\"$table"."` 
where 1
and `\".DB_PREFIX.\"$table"."`.`$edit_key` = '\$edit_key'
\";
\$res = mysql_query(\$sql);
if(mysql_num_rows(\$res))
{
\$data = mysql_fetch_assoc(\$res);
";
$str .= "?".">
";
$str .= "<form name=\"form$table\" id=\"form$table\" action=\"\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"return checkForm(this, '".$lpack['tip_needed']."')\">
";
// FORM
$str .= "\t<table width=\"800\" border=\"0\" class=\"two-side-table\" cellspacing=\"0\" cellpadding=\"0\">
";
foreach($field_edit as $k=>$field2)
{
	$caption = @$_POST['caption_'.$field2];
	if($k==0) $width = " width=\"200\"";
	else $width = "";
	$str .= "\t\t<tr>\r\n\t\t<td$width>".$caption."</td>\r\n\t\t<td>"; 
	$nd = (in_array($field2, $needed))?" data-required=\"true\"":"";
	switch($_POST['inputtype_'.$field2])
	{
		case 'text':
		$type2 = 'text';
		$attr = '';
		switch($_POST['data_type_'.$field2])
		{
			case 'date':
			$class = ' input-text-date';
			$label = ' '.$lpack['label_yyyy_mm_dd'];
			break;
			case 'time':
			$class = ' input-text-time';
			$label = ' '.$lpack['label_hh_ii_ss'];
			break;
			case 'datetime':
			$class = ' input-text-datetime';
			$label = ' '.$lpack['label_yyyy_mm_dd_hh_ii_ss'];
			break;
			case 'text':
			$class = ' input-text-long';
			$label = '';
			break;
			case 'email':
			$class = ' input-text-long';
			$label = '';
			$attr = 'data-type="email" ';
			break;
			case 'password':
			$class = ' input-text-long';
			$label = '';
			$type2 = 'password';
			break;
			case 'int':
			$class = ' input-text-medium';
			$label = '';
			$type2 = 'number';
			break;
			case 'float':
			$class = ' input-text-medium';
			$label = '';
			$type2 = 'number" step="any';
			break;
			default:
			$class = '';
			$label = '';
			$type2 = 'text';
			break;
		}
		$str .= "<input type=\"$type2\" class=\"input-text$class\" name=\"$field2\" id=\"$field2\" value=\"<"."?php echo (\$data["."'$field2'"."]);?".">\"$nd autocomplete=\"off\" $attr/>$label";
		break;
		case 'textarea':
		$str .= "<textarea class=\"input-textarea\" name=\"$field2\" id=\"$field2\"$nd><"."?php echo (\$data["."'$field2'"."]);?"."></textarea>";
		break;
		case 'menu':
		$table_name = $field2;
		$idp = stripos($table_name, "_id");
		if($idp === strlen($table_name)-3)
		{
			$table_name = substr($table_name, 0, strlen($table_name)-3);
		}
		$str .= "<select class=\"input-select\" name=\"$field2\" id=\"$field2\"$nd>
		<option value=\"\"></option>
		<"."?php echo selectoption('$table_name', '$field2', NULL, \$data['$field2']); ?".">\r\n\t\t</select>";
		break;
		case 'checkbox10':
		$str .= "<label><input type=\"checkbox\" class=\"input-checkbox\" name=\"$field2\" value=\"1\" id=\"$field2\"$nd<?php if(\$data['$field2']==1) echo ' checked=\"checked\"';?>> ".ucwords(strtolower(str_replace("_"," ",$field2)))."</label>
		";
		break;
		case 'enum':
		$tab = addslashes($_POST['table']);
		$opt = createEnumOption($tab, $field2);
		
		$str .= "<select class=\"input-select\" name=\"$field2\" id=\"$field2\"$nd>
		<option value=\"\"></option>\r\n";
		$ret = "";
		foreach($opt as $k=>$v)
		{
			$sel = "<"."?php if(\$data['".$field2."'] == '".$v[1]."') echo \" selected=\\\"selected\\\"\";?".">";
			$ret .= "\t\t<option value=\"".$v[1]."\"$sel>".$v[0]."</option>\r\n";
		}
		$str .= $ret;
		$str .= "\t\t</select>";
		break;
		
	}
	if($k==0)
	{
	$str .= "<input type=\"hidden\" name=\"$edit_key"."2\" id=\"$edit_key"."2\" value=\"<"."?php echo (\$data["."'$edit_key'"."]);?".">\" />";
	}
	$str .="</td>\r\n\t\t</tr>\r\n";			  
}
$str .= "\t\t<tr><td>&nbsp;</td>\r\n\t\t<td><input type=\"submit\" name=\"save\" id=\"save\" class=\"com-button\" value=\"".$lpack['button_save']."\" /> <input type=\"button\" name=\"showall\" id=\"showall\" value=\"".$lpack['button_show_all']."\" class=\"com-button\" onclick=\"window.location='<?php echo basename(\$_SERVER['PHP_SELF']);?>'\" /></td>\r\n\t\t</tr>\r\n";
$str .= "\t</table>
";
$str .= "</form>
";
$str .= "<"."?php
}
else
{
";
$str .= "?".">
";
$str .= "<div class=\"warning\">".$lpack['message_search_data_not_found']." <a href=\"<?php echo basename(\$_SERVER['PHP_SELF']);?>\">".$lpack['message_search_link_back']."</a></div>	
";
$str .= "<"."?php
}
include_once dirname(__FILE__).\"/lib.inc/footer.php\";
";
$str .= "
}
";
$str .= "else if(@\$_GET"."['option']"."=='detail')
{
";
$str .= "include_once dirname(__FILE__).\"/lib.inc/header.php\";
\$edit_key = kh_filter_input(INPUT_GET, '$edit_key', $edit_key_type);
\$nt = '';
";

if(count($field_normalization))
{
$arr_norm = array();
foreach($field_normalization as $k=>$val)
{
	$arr_norm[] = "'".$val."'=>'".$val."'";
}
$str .= "\$nt = createSubSelect(DB_PREFIX.'$table', array(".implode(",",$arr_norm)."));
";
}
$str .= "\$sql = \"select `\".DB_PREFIX.\"$table"."`.* \$nt
from `\".DB_PREFIX.\"$table"."` 
where 1
and `\".DB_PREFIX.\"$table"."`.`$edit_key` = '\$edit_key'
\";
\$res = mysql_query(\$sql);
if(mysql_num_rows(\$res))
{
\$data = mysql_fetch_assoc(\$res);
";
$str .= "?".">
";
$str .= "<form name=\"form$table\" action=\"\" method=\"post\" enctype=\"multipart/form-data\">
";
// FORM
$str .= "\t<table width=\"800\" border=\"0\" class=\"two-side-table\" cellspacing=\"0\" cellpadding=\"0\">
";
foreach($field_detail as $k=>$field2)
{
	$caption = @$_POST['caption_'.$field2];
	if($k==0) $width = " width=\"200\"";
	else $width = "";
	$str .= "\t\t<tr>\r\n\t\t<td$width>$caption</td>\r\n\t\t<td>"; 
	if(@$_POST['inputtype_'.$field2] == 'checkbox10')
	{
	$str .= "<"."?php echo (\$data["."'$field2'"."])?'".$lpack['label_yes']."':'".$lpack['label_no']."';?".">";
	}
	else
	{
	$str .= "<"."?php echo (\$data["."'$field2'"."]);?".">";
	}
	$str .="</td>\r\n\t\t</tr>\r\n";			  
}
$str .= "\t\t<tr>\r\n\t\t<td>&nbsp;</td>\r\n\t\t<td><input type=\"button\" name=\"edit\" id=\"edit\" class=\"com-button\" value=\"".$lpack['button_edit']."\" onclick=\"window.location='<"."?php echo basename(\$_SERVER"."['PHP_SELF']".");?>?option=edit&$edit_key=<?php echo \$data['$edit_key'];?>'\" /> <input type=\"button\" name=\"showall\" id=\"showall\" value=\"".$lpack['button_show_all']."\" class=\"com-button\" onclick=\"window.location='<?php echo basename(\$_SERVER['PHP_SELF']);?>'\" /></td>\r\n\t\t</tr>\r\n";
$str .= "\t</table>
";
$str .= "</form>
";
$str .= "<"."?php
}
else
{
";
$str .= "?".">
";
$str .= "<div class=\"warning\">".$lpack['message_search_data_not_found']." <a href=\"<?php echo basename(\$_SERVER['PHP_SELF']);?>\">".$lpack['message_search_link_back']."</a></div>	
";
$str .= "<"."?php
}
include_once dirname(__FILE__).\"/lib.inc/footer.php\";
";
$str .= "
}
";
$str .= "else
{
include_once dirname(__FILE__).\"/lib.inc/header.php\";
?".">
";
$str .= "<div class=\"search-control\">
<form id=\"searchform\" name=\"form1\" method=\"get\" action=\"\">
  ".ucwords(strtolower(str_replace("_"," ",$table)))."
    <input type=\"text\" name=\"q\" id=\"q\" autocomplete=\"off\" class=\"input-text input-text-search\" value=\"<?php echo htmlspecialchars(rawurldecode((trim(@\$_GET['q'],\" \t\r\n \"))));?>\" />
  <input type=\"submit\" name=\"search\" id=\"search\" value=\"".$lpack['button_search']."\" class=\"com-button\" />
</form>
</div>
<div class=\"search-result\">
";
$str .= "<?php
\$sql_filter = \"\";
\$pagination->array_get = array();
if(\$pagination->query){
\$pagination->array_get[] = 'q';
\$sql_filter .= \" and (`\".DB_PREFIX.\"$table`.`nama` like '%\".addslashes(\$pagination->query).\"%' )\";
}


\$nt = '';
";

if(count($field_normalization_row))
{
$arr_norm = array();
foreach($field_normalization_row as $k=>$val)
{
	$arr_norm[] = "'".$val."'=>'".$val."'";
}
$str .= "
\$nt = createSubSelect(DB_PREFIX.'$table', array(".implode(",",$arr_norm)."));
";
}
$str .= "
\$sql = \"select `\".DB_PREFIX.\"$table"."`.* \$nt
from `\".DB_PREFIX.\"$table"."`
where 1 \$sql_filter
order by `\".DB_PREFIX.\"$table"."`.`$edit_key` asc
\";
\$sql_test = \"select `\".DB_PREFIX.\"$table"."`.*
from `\".DB_PREFIX.\"$table"."`
where 1 \$sql_filter
\";
\$res = mysql_query(\$sql_test);
\$pagination->total_record = mysql_num_rows(\$res);
\$res = mysql_query(\$sql.\$pagination->limit_sql);
\$pagination->total_record_with_limit = mysql_num_rows(\$res);
if(\$pagination->total_record_with_limit)
{
\$pagination->start = \$pagination->offset+1;
\$pagination->end = \$pagination->offset+\$pagination->total_record_with_limit;

\$pagination->result = $picoEdu->createPagination(basename(\$_SERVER['PHP_SELF']), \$pagination->total_record, \$pagination->limit, \$pagination->num_page, 
\$pagination->offset, \$pagination->array_get, true, \$pagination->str_first, \$pagination->str_last, \$pagination->str_prev, \$pagination->str_next); 
\$pagination->str_result = \"\";
foreach(\$pagination->result as \$i=>\$obj)
{
\$cls = (\$obj->sel)?\" class=\\\"pagination-selected\\\"\":\"\";
\$pagination->str_result .= \"<a href=\\\"\".\$obj->ref.\"\\\"\$cls>\".\$obj->text.\"</a> \";
}
?>
<form name=\"form1\" method=\"post\" action=\"\">

<div class=\"search-pagination search-pagination-top\">
<div class=\"search-pagination-control\"><?php echo \$pagination->str_result;?></div>
<div class=\"search-pagination-label\">".$lpack['label_row']." <?php echo \$pagination->start;?> ".$lpack['label_to']." <?php echo \$pagination->end;?> ".$lpack['label_from']." <?php echo \$pagination->total_record;?></div>
</div>

  <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"row-table\">
  <thead>
    <tr>
      <td width=\"16\"><input type=\"checkbox\" name=\"control-$edit_key\" id=\"control-$edit_key\" class=\"checkbox-selector\" data-target=\".$edit_key\" value=\"1\"></td>
      <td width=\"16\"><img src=\"tools/images/trans.gif\" class=\"icon-16 icon-edit-16\" alt=\"Edit\" border=\"0\" /></td>
      <td width=\"25\">No</td>\r\n";
for($i=0; $i<count($field_row); $i++)
{
	$field2 = $field_row[$i];
	$caption = @$_POST['caption_'.$field2];
	$str .= "      <td>$caption</td>\r\n";
}
$str .="</tr>
    </thead>
    <tbody>
    <?php
	\$no=\$pagination->offset;
	while((\$data = mysql_fetch_assoc(\$res)))
	{
	\$no++;
	?>
    <tr<?php echo (@\$data['active'])?\" class=\\\"data-active\\\"\":\" class=\\\"data-inactive\\\"\";?>>
      <td><input type=\"checkbox\" name=\"$edit_key"."[]\" id=\"$edit_key\" value=\"<?php echo \$data"."['$edit_key'];?>\" class=\"$edit_key\" /></td>
      <td><a href=\"<?php echo basename(\$_SERVER['PHP_SELF']);?>?option=edit&$edit_key=<?php echo \$data['$edit_key'];?>\"><img src=\"tools/images/trans.gif\" class=\"icon-16 icon-edit-16\" alt=\"Edit\" border=\"0\" /></a></td>
      <td align=\"right\"><?php echo \$no;?></td>\r\n";
	for($i=0; $i<count($field_row); $i++)
	{
		$field2 = $field_row[$i];
		if(@$_POST['inputtype_'.$field2] == 'checkbox10')
		{
		$str .= "      <td><"."?php echo (\$data["."'$field2'"."])?'".$lpack['label_yes']."':'".$lpack['label_no']."';?"."></td>\r\n";
		}
		else
		{
		$str .= "      <td><a href=\"<?php echo basename(\$_SERVER['PHP_SELF']);?>?option=detail&$edit_key=<?php echo \$data['$edit_key'];?>\"><?php echo (\$data['$field2']);?></a></td>\r\n";
		}
	}
$str .="     </tr>
    <?php
	}
	?>
    </tbody>
  </table>

<div class=\"search-pagination search-pagination-bottom\">
<div class=\"search-pagination-control\"><?php echo \$pagination->str_result;?></div>
<div class=\"search-pagination-label\">".$lpack['label_row']." <?php echo \$pagination->start;?> ".$lpack['label_to']." <?php echo \$pagination->end;?> ".$lpack['label_from']." <?php echo \$pagination->total_record;?></div>
</div>

<div class=\"button-area\">
  <input type=\"submit\" name=\"set_active\" id=\"set_active\" value=\"".$lpack['button_activate']."\" class=\"com-button\" />
  <input type=\"submit\" name=\"set_inactive\" id=\"set_inactive\" value=\"".$lpack['button_deactivate']."\" class=\"com-button\" />
  <input type=\"submit\" name=\"delete\" id=\"delete\" value=\"".$lpack['button_delete']."\" class=\"com-button delete-button\" onclick=\"return confirm('".$lpack['message_confirm_delete_record']."');\" />
  <input type=\"button\" name=\"add\" id=\"add\" value=\"".$lpack['button_add']."\" class=\"com-button\" onclick=\"window.location='<?php echo basename(\$_SERVER['PHP_SELF']);?>?option=add'\" />
  </div>
</form>
<?php
}
else if(@\$_GET['q'])
{
?>
<div class=\"warning\">".$lpack['message_search_no_result']."</div>
<?php
}
else
{
?>
<div class=\"warning\">".$lpack['message_search_data_not_found']." <a href=\"<?php echo basename(\$_SERVER['PHP_SELF']);?>?option=add\">".$lpack['message_search_link_add_new']."</a></div>
<?php
}
?>
";
$str .= "</div>
";
$str .= "
<"."?php
include_once dirname(__FILE__).\"/lib.inc/footer.php\";
}
";
$str .= "?".">";
$str = str_replace(" Id<", " ID<", $str);
file_put_contents(dirname(__FILE__)."/".$_POST['filename'], $str);
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Form</title>
<style type="text/css">
body{
	font-family:Tahoma, Geneva, sans-serif;
	font-size:12px;
}
.row-table tr:hover{
	color:#F00;
}
.row-table td{
	padding:3px 4px;
	white-space:nowrap;
}
.row-table thead td{
	background-color:#06F;
	color:#FFFFFF;
}
.input-text{
	border:1px solid #EEEEEE;
	background-color:#FFFFFF;
	padding:3px;
	color:#333333;
	width:100px;
}
.enum-disabled{
	color:#999999;
}
</style>
</head>

<body>
<div class="all">
<div class="header">
<form id="form1" name="form1" method="post" action="">
  Table 
    <input type="text" name="table" id="table" />
  <input type="submit" name="show" id="show" value="Show" />
</form>
</div>
<div class="body">
<?php
if(@$_POST['show'])
{
	$table = trim($_POST['table']);
	$sql = "show columns from `$table` ";
	$res = mysql_query($sql);
?>
<script type="text/javascript" src="script/jquery/jquery.min.js"></script>
<script type="text/javascript">
function submitForm(frm)
{
	var data = $(frm).serialize();
	$('#data2send').val(data);
	$('#form2submit').submit();
	return false;
}
</script>
<form id="form2submit" name="form2submit" enctype="multipart/form-data" method="post">
<input type="hidden" id="data2send" name="data2send" value="" />
</form>
<form id="form2" name="form2" method="post" action="" enctype="multipart/form-data" onSubmit="return submitForm(this)">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="row-table">
<thead>
  <tr>
  <td>Field</td>
  <td>I</td>
  <td>E</td>
  <td>D</td>
  <td>R</td>
  <td>K</td>
  <td>N</td>
  <td colspan="6">Field Type</td>
  <td>Caption</td>
  <td>Data Type</td>
  <td>Filter</td>
  </tr>
  </thead>
  <tbody>
    <?php
	while(($data = mysql_fetch_assoc($res)))
	{
		if(strpos($data['Type'], 'enum')===0)
		{
			$typeenum = true;
		}
		else
		{
			$typeenum = false;
		}
	?>
    <tr>
      <td><?php echo $data['Field'];?><input type="hidden" name="field[]" id="field" value="<?php echo $data['Field'];?>" /></td>
      <td><input class="checkbox-insert" type="checkbox" name="include_new_<?php echo $data['Field'];?>" id="include_new_<?php echo $data['Field'];?>" value="1" checked="checked" /></td>
      <td><input class="checkbox-edit" type="checkbox" name="include_edit_<?php echo $data['Field'];?>" id="include_edit_<?php echo $data['Field'];?>" value="1" checked="checked" /></td>
      <td><input class="checkbox-detail" type="checkbox" name="include_detail_<?php echo $data['Field'];?>" id="include_detail_<?php echo $data['Field'];?>" value="1" checked="checked" /></td>
      <td><input class="checkbox-row" type="checkbox" name="include_row_<?php echo $data['Field'];?>" id="include_row_<?php echo $data['Field'];?>" value="1" checked="checked" /></td>
      <td><input class="checkbox-key" type="checkbox" name="include_key_<?php echo $data['Field'];?>" id="include_key_<?php echo $data['Field'];?>" value="1"<?php if($data['Key']) echo ' checked="checked"';?> /></td>
      <td><input class="checkbox-needed" type="checkbox" name="needed_<?php echo $data['Field'];?>" id="needed_<?php echo $data['Field'];?>" value="1" />
      </td>
      <td><label><input type="radio" name="inputtype_<?php echo $data['Field'];?>" id="inputtype_<?php echo $data['Field'];?>" value="text"<?php if(!$typeenum) echo ' checked="checked"';?> /> 
      TE</label></td>
      <td><label><input type="radio" name="inputtype_<?php echo $data['Field'];?>" id="inputtype_<?php echo $data['Field'];?>" value="textarea" /> 
      TA</label></td>
      <td><label><input type="radio" name="inputtype_<?php echo $data['Field'];?>" id="inputtype_<?php echo $data['Field'];?>" value="menu" /> 
      ME</label></td>
      <td><label><input type="radio" name="inputtype_<?php echo $data['Field'];?>" id="inputtype_<?php echo $data['Field'];?>" value="checkbox10" />
      YN
      </label></td>
      <td><label><input type="radio" name="inputtype_<?php echo $data['Field'];?>" id="inputtype_<?php echo $data['Field'];?>" value="radio" /> 
      RA</label></td>
      <td><span class="enum<?php if(!$typeenum) echo ' enum-disabled';?>"><label><input type="radio" name="inputtype_<?php echo $data['Field'];?>" id="inputtype_<?php echo $data['Field'];?>" value="enum"<?php if(!$typeenum) echo ' disabled="disabled"'; else echo ' checked="checked"';?> /> 
      EN</label></span></td>
      <td><input type="text" class="input-text" name="caption_<?php echo $data['Field'];?>" id="caption_<?php echo $data['Field'];?>" value="<?php $caption = ucwords(str_replace(array("_", "-"), " ",$data['Field'])); $idp = stripos($caption, " Id"); if($idp === strlen($caption)-3) $caption = substr($caption, 0, strlen($caption)-3); echo $caption;?>" /></td>
      <td><select name="data_type_<?php echo $data['Field'];?>" id="data_type_<?php echo $data['Field'];?>"><?php echo filterDataType($data['Type'], $data['Field']);?></select></td>
      <td><select name="filter_<?php echo $data['Field'];?>" id="filter_<?php echo $data['Field'];?>"><?php echo createFilter($data['Type']);?></select></td>
    </tr>
    <?php
	}
	?>
    </tbody>
  </table>
  <table width="400" border="0" cellpadding="0" cellspacing="0">
  <tr>
  <td>Language</td>
  <td><select name="lang" id="lang">
  <option value="en">English</option>
  <option value="id" selected="selected">Indonesia</option>
  </select></td>
  </tr>
  <tr>
  <td width="120">
  File Name
  <input type="hidden" name="table" id="table" value="<?php echo $table;?>" />
  </td>
  <td>
  <input type="text" name="filename" id="filename" value="--<?php echo $table;?>.php" /> <input type="submit" name="save" id="save" value="Save" />
  </td>
  </tr>
  </table>
</form>
<script type="text/javascript">
$('.checkbox-needed').filter(':first').attr('checked', 'checked');
$('.checkbox-needed').filter('[name=needed_nama]').attr('checked', 'checked');
$('#filter_default').val('FILTER_SANITIZE_NUMBER_UINT');
$('#filter_aktif').val('FILTER_SANITIZE_NUMBER_UINT');
$('#inputtype_default[value=checkbox10]').attr('checked', 'checked');
$('#inputtype_aktif[value=checkbox10]').attr('checked', 'checked');
</script>
<?php
}
?>
</div>
</div>
</body>
</html>