<?php
function createPagination($module, $totalrecord, $resultperpage = 1, $numberofpage = 1, $offset = 0, $arrayget, $showfirstandlast = true, $firstCaption = "First", $lastCaption = "Last", $prevCaption = "Prev", $nextCaption = "Next")
	{
		$result = array();
		$result[0] = new StdClass();
		$result[1] = new StdClass();
		$arg = "";
		$paginationObject = new StdClass();
		$paginationObject->text = "";
		$paginationObject->ref = "";
		if ($totalrecord <= $resultperpage) {
			return array();
		}
		if (!is_array($arrayget)) {
			$arrayget = array($arrayget);
		}
		foreach ($arrayget as $item) {
			$arg .= "&$item=" . @$_GET[$item];
		}
		$arg = "$module?" . trim($arg, "&");
		$curpage = abs(ceil($offset / $resultperpage)) + 1;
		$startpage = abs(ceil($curpage - floor($numberofpage / 2)));
		if ($startpage < 1) {
			$startpage = 1;
		}
		$endpage = $startpage + $numberofpage - 1;
		$lastpage = ceil($totalrecord / $resultperpage);


		if ($endpage > $lastpage) {
			$endpage = $lastpage;
		}
		$paginationObject->text = "";
		$paginationObject->ref = "";
		$paginationObject->ref_first = 0;
		$paginationObject->str_first = $firstCaption;
		$paginationObject->str_prev = $prevCaption;
		$paginationObject->ref_prev = ($curpage - 2) * $resultperpage;
		if ($paginationObject->ref_prev < 0) {
			$paginationObject->ref_prev = 0;
		}
		$paginationObject->str_next = $nextCaption;
		$paginationObject->ref_next = ($curpage) * $resultperpage;
		$paginationObject->str_last = $lastCaption;
		$paginationObject->ref_last = floor($totalrecord / $resultperpage) * $resultperpage;
		if ($paginationObject->ref_last == $totalrecord) {
			$paginationObject->ref_last = $totalrecord - $resultperpage;
		}

		$result[0]->text = $paginationObject->str_first;
		$result[0]->ref = str_replace("?&", "?", $arg . "&offset=" . $paginationObject->ref_first);
		$result[0]->sel = false;
		if ($curpage >= 0) {
			$result[1]->text = $paginationObject->str_prev;
			$result[1]->ref = str_replace("?&", "?", $arg . "&offset=" . $paginationObject->ref_prev);
			$result[1]->sel = 0;
		}
		for ($j = 2, $i = $startpage; $i <= ($endpage); $i++, $j++) {
			$pn = $i;
			$result[$j] = new StdClass();
			$result[$j]->text = "$pn";
			$result[$j]->ref = str_replace("?&", "?", $arg . "&offset=" . (($i - 1) * $resultperpage));
			if ($curpage == $i) {
				$result[$j]->sel = true;
			} else {
				$result[$j]->sel = false;
			}
		}
		if ($endpage < $lastpage) {
			$result[$j] = new StdClass();
			$result[$j]->text = $paginationObject->str_next;
			$result[$j]->ref = str_replace("?&", "?", $arg . "&offset=" . $paginationObject->ref_next);
			$result[$j]->sel = false;
			$j++;
		}
		$result[$j] = new StdClass();
		$result[$j]->text = $paginationObject->str_last;
		$result[$j]->ref = str_replace("?&", "?", $arg . "&offset=" . $paginationObject->ref_last);
		$result[$j]->sel = false;
		return $result;
	}
