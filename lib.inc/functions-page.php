<?php
include_once dirname(dirname(__FILE__))."/inc-cfg.php";


function menu_load(PicoDatabase $database, $member_id, $parent=0, $grade=1){
	$sql = "SELECT `m1`.* , 
	(select count(distinct `m2`.`menu_id`) from `menu` as `m2` where `m2`.`parent` = `m1`.`menu_id`) as `numchild`,
	`page`.`page_id` as `page_id`, `page`.`permalink` as `page_permalink`
	from `menu` as `m1`
	left join (`page`) on ( `page`.`permalink` = `m1`.`permalink` and  `page`.`type` = '2')
	where `m1`.`member_id` = '$member_id' and `m1`.`parent` = '$parent' group by `m1`.`menu_id`
	order by `m1`.`order` asc, `m1`.`menu_id` asc ";
	
	$ret = array();
	$stmt = $database->executeQuery($sql);
	if($stmt->rowCount() > 0)
	{
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $data) {
			unset($obj);
			$obj = new StdClass();
			$obj->id = $data['menu_id'];
			$obj->caption = $data['name'];
			$obj->link = $data['permalink'];
			$obj->page = $data['permalink'];
			$obj->page_id = $data['page_id'];


			$obj->page_link = $data['permalink'];
			$obj->order = $data['order'];
			$obj->grade = $grade;
			$obj->numchild = $data['numchild'];
			if ($data['numchild']) {
				$obj->child = menu_load($member_id, $data['menu_id'], $grade + 1);
			}
			$ret[] = $obj;
		}
	}
	return $ret;
	
}

function menu_build_for_page($res){
	$html = "";
	$html .= "<ul>\r\n";
	if(is_array($res))
	{
		foreach($res as $k=>$val)
		{
			$lnk = $val->link;
			$html .= "<li>".
			"<a href=\"$lnk\">".
			$val->caption.
			"</a>";
			if($val->numchild)
			{
				$html .= menu_build_for_page($val->child);
			}
			$html .= "</li>\r\n";
		}
	}
	$html .= "</ul>";
	return $html;
}
function menu_build_for_edit($res){
	$html = "";
	$html .= "<ul>\r\n";
	if(is_array($res))
	{
		foreach($res as $k=>$val)
		{
			if($val->page_id)
			{
				$cls = "menu-has-page";
			}
			else if(stripos($val->link, "?page=")===0)
			{
				$cls = "menu-has-no-page";
			}
			else if($val->link)
			{
				$cls = "menu-has-link";
			}
			else 
			{
				$cls = "menu-blank";
			}
			$html .= "<li><input type=\"checkbox\" name=\"menuid[]\" id=\"menuid\" value=\"".$val->id."\"> ".
			"<a href=\"".basename(kh_filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_STRING_NEW)).'?action=edit&id='.$val->id."\" data-menu-id=\"".$val->id."\" data-page-id=\"".$val->page_id."\" data-page-link=\"".$val->page_link."\" data-link=\"".$val->link."\" class=\"$cls\">".
			$val->caption.
			"</a>";
			if($val->numchild)
			{
				$html .= menu_build_for_edit($val->child);
			}
			$html .= "</li>\r\n";
		}
	}
	$html .= "</ul>";
	return $html;
}
function menu_build_for_select($res, $value=''){
	$html = "";
	if(is_array($res))
	{
		foreach($res as $k=>$val)
		{
			$sel = '';
			if(strlen($value) && $value==$val->id)
			{
				$sel = ' selected="selected"';
			}
			$html .= "<option value=\"".$val->id."\"$sel>".
			str_repeat("&nbsp;", 3*($val->grade-1)).$val->caption.
			"</option>\r\n";
			if($val->numchild)
			{
				$html .= menu_build_for_select($val->child, $value);
			}
		}
	}
	return $html;
}

function menu_list_inline($res){
	$arr = array();
	if(is_array($res))
	{
		foreach($res as $k=>$val)
		{
			$arr[] = $val->id;
			if($val->numchild)
			{
				$arr2 = menu_list_inline($val->child);
				foreach($arr2 as $v)
				{
					$arr[] = $v;
				}
			}
		}
	}
	return $arr;
}
function menu_list_inline_link($res){
	$arr = array();
	if(is_array($res))
	{
		foreach($res as $k=>$val)
		{
			$arr[] = $val->link;
			if($val->numchild)
			{
				$arr2 = menu_list_inline_link($val->child);
				foreach($arr2 as $v)
				{
					$arr[] = $v;
				}
			}
		}
	}
	return $arr;
}



?>