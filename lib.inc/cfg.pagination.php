<?php
class PicoPagination
{
	const RAQUO = ' &raquo; ';
	const AMPERSAND_OFFSET = '&offset=';

    public $start = 0;
    public $end = 0;
    public $result = "";
    public $limit = 20;
    public $num_page = 4;
    public $total_record = 0;
    public $total_record_with_limit = 0;

    public $str_first = '&laquo;';//'<span class="pagination-icon first"></span>';//$lang_pack['pagination_first'];
    public $str_last = '&raquo;';//'<span class="pagination-icon last"></span>';//$lang_pack['pagination_last'];
    public $str_prev = '&lsaquo;';//'<span class="pagination-icon prev"></span>';//$lang_pack['pagination_prev'];
    public $str_next = '&rsaquo;';//'<span class="pagination-icon next"></span>';//$lang_pack['pagination_next'];
    public $str_noresult = 'Pencarian tidak menemukan hasil.';//$lang_pack['pagination_noresult'];
    public $str_nodata = 'Data tidak ditemukan.';//$lang_pack['pagination_nodata'];
    public $str_nodata_add = 'Data tidak ditemukan. <a href="%s">Klik di sini untuk membuat baru</a>.';//$lang_pack['pagination_nodata_add'];
    public $str_keyword = 'Kata kunci';//$lang_pack['search_label'];
    public $str_search = 'Cari';//$lang_pack['search_button_search'];
    public $str_soundlike = 'Cari seperti';//$lang_pack['search_button_soundlike'];
    public $str_record = 'Baris';//$lang_pack['pagination_record'];
    public $str_from = 'dari';//$lang_pack['pagination_from'];
    public $str_to = 'hingga';//$lang_pack['pagination_to'];
    public $str_of = 'dari';//$lang_pack['pagination_of'];

    public $query = '';
    public $query_edit = '';
    public $offset = '';

    public $limit_sql = '';
    public $str_result = "";


    public $array_get = array();

    public function __construct()
    {
        $this->query = trim(kh_filter_input(INPUT_GET, "q", FILTER_SANITIZE_SPECIAL_CHARS));
        $this->query_edit = kh_filter_input(INPUT_GET, "q");
        $this->query_edit = trim(htmlspecialchars($this->query_edit));
        $this->offset = kh_filter_input(INPUT_GET, "offset", FILTER_SANITIZE_NUMBER_UINT);
        $this->start = $this->offset + 1;
    
        $this->limit_sql = " limit ".$this->offset.", ".$this->limit;
        $this->str_result = "";          
    }

    public function appendQueryName($queryParameterName)
    {
        $this->array_get[] = $queryParameterName;
    }

    public function setTotalRecord($totalRecord)
    {
        $this->total_record = $totalRecord;
    }

    public function setTotalRecordWithLimit($totalRecordWithLimit)
    {
        $this->total_record_with_limit = $totalRecordWithLimit;
        $this->end = $this->offset + $this->total_record_with_limit;
    }
    public function getTotalRecordWithLimit()
    {
        return $this->total_record_with_limit;
    }
    public function createPagination($module, $totalrecord, $resultperpage = 1, $numberofpage = 1, $offset = 0, $showfirstandlast = true) //NOSONAR
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
        
        foreach ($this->array_get as $item) {
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
        $paginationObject->str_first = $this->str_first;
        $paginationObject->str_prev = $this->str_prev;
        $paginationObject->ref_prev = ($curpage - 2) * $resultperpage;
        if ($paginationObject->ref_prev < 0) {
            $paginationObject->ref_prev = 0;
        }
        $paginationObject->str_next = $this->str_next;
        $paginationObject->ref_next = ($curpage) * $resultperpage;
        $paginationObject->str_last = $this->str_last;
        $paginationObject->ref_last = floor($totalrecord / $resultperpage) * $resultperpage;
        if ($paginationObject->ref_last == $totalrecord) {
            $paginationObject->ref_last = $totalrecord - $resultperpage;
        }

        $result[0]->text = $paginationObject->str_first;
        $result[0]->ref = str_replace("?&", "?", $arg . self::AMPERSAND_OFFSET . $paginationObject->ref_first);
        $result[0]->sel = false;
        if ($curpage >= 0) {
            $result[1]->text = $paginationObject->str_prev;
            $result[1]->ref = str_replace("?&", "?", $arg . self::AMPERSAND_OFFSET . $paginationObject->ref_prev);
            $result[1]->sel = 0;
        }
        for ($j = 2, $i = $startpage; $i <= ($endpage); $i++, $j++) {
            $pn = $i;
            $result[$j] = new StdClass();
            $result[$j]->text = "$pn";
            $result[$j]->ref = str_replace("?&", "?", $arg . self::AMPERSAND_OFFSET . (($i - 1) * $resultperpage));
            if ($curpage == $i) {
                $result[$j]->sel = true;
            } else {
                $result[$j]->sel = false;
            }
        }
        if ($endpage < $lastpage) {
            $result[$j] = new StdClass();
            $result[$j]->text = $paginationObject->str_next;
            $result[$j]->ref = str_replace("?&", "?", $arg . self::AMPERSAND_OFFSET . $paginationObject->ref_next);
            $result[$j]->sel = false;
            $j++;
        }
        $result[$j] = new StdClass();
        $result[$j]->text = $paginationObject->str_last;
        $result[$j]->ref = str_replace("?&", "?", $arg . self::AMPERSAND_OFFSET . $paginationObject->ref_last);
        $result[$j]->sel = false;
        return $result;
    }

    public function createPaginationHtml()
	{
		$str_result = "";
		if(is_array($this->result))
		{
			$str_result .= '
			<nav aria-label="Page navigation example">
			<ul class="pagination">';
				foreach($this->result as $obj)
			{
				$cls = ($obj->sel)?" active":"";
				$str_result .= '
				<li class="page-item'.$cls.'"><a class="page-link" href="'.$obj->ref.'">'.$obj->text.'</a></li>';
			}
			$str_result .= '
			</ul>
			</nav>
			';
			return $str_result;
		}
		return "";
	}
 
}



$pagination = new \PicoPagination();