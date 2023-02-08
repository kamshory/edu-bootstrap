<?php
class PicoPagination
{
	const RAQUO = ' &raquo; ';
	const AMPERSAND_OFFSET = '&offset=';

    private $start = 0;
    private $end = 0;
    private $result = "";
    private $limit = 20;
    private $num_page = 3;
    private $total_record = 0;
    private $total_record_with_limit = 0;

    private $str_first = '&laquo;';//'<span class="pagination-icon first"></span>';//$lang_pack['pagination_first'];
    private $str_last = '&raquo;';//'<span class="pagination-icon last"></span>';//$lang_pack['pagination_last'];
    private $str_prev = '&lsaquo;';//'<span class="pagination-icon prev"></span>';//$lang_pack['pagination_prev'];
    private $str_next = '&rsaquo;';//'<span class="pagination-icon next"></span>';//$lang_pack['pagination_next'];
    private $str_noresult = 'Pencarian tidak menemukan hasil.';//$lang_pack['pagination_noresult'];
    private $str_nodata = 'Data tidak ditemukan.';//$lang_pack['pagination_nodata'];
    private $str_nodata_add = 'Data tidak ditemukan. <a href="%s">Klik di sini untuk membuat baru</a>.';//$lang_pack['pagination_nodata_add'];
    private $str_keyword = 'Kata kunci';//$lang_pack['search_label'];
    private $str_search = 'Cari';//$lang_pack['search_button_search'];
    private $str_soundlike = 'Cari seperti';//$lang_pack['search_button_soundlike'];
    private $str_record = 'Baris';//$lang_pack['pagination_record'];
    private $str_from = 'dari';//$lang_pack['pagination_from'];
    private $str_to = 'hingga';//$lang_pack['pagination_to'];
    private $str_of = 'dari';//$lang_pack['pagination_of'];

    private $query = '';
    private $query_edit = '';
    private $offset = '';

    private $limit_sql = '';


    private $array_get = array();

    public function __construct()
    {
        $this->query = trim(kh_filter_input(INPUT_GET, "q", FILTER_SANITIZE_SPECIAL_CHARS));
        $this->query_edit = kh_filter_input(INPUT_GET, "q");
        $this->query_edit = trim(htmlspecialchars($this->query_edit));
        $this->offset = kh_filter_input(INPUT_GET, "offset", FILTER_SANITIZE_NUMBER_UINT);
        $this->start = $this->offset + 1;
        $this->limit_sql = " limit ".$this->offset.", ".$this->limit;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getLimitSql()
    {
        return $this->limit_sql;
    }

    public function setLimitSql($sqlLimit)
    {
        $this->limit_sql = $sqlLimit;
    }

    public function getOffset()
    {
        return $this->offset;
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

    public function getResultInfo()
    {
        return $this->start.'-'.$this->end.'/'.$this->total_record;
    }
    
    public function createPagination($module, $showfirstandlast = true) //NOSONAR
    {
        $result = array();
        $result[0] = new StdClass();
        $result[1] = new StdClass();
        $arg = "";
        $paginationObject = new StdClass();
        $paginationObject->text = "";
        $paginationObject->ref = "";
        if ($this->total_record <= $this->limit) {
            return array();
        }
        
        foreach ($this->array_get as $item) {
            $arg .= "&$item=" . @$_GET[$item];
        }
        $arg = "$module?" . trim($arg, "&");
        $curpage = abs(ceil($this->offset / $this->limit)) + 1;
        $startpage = abs(ceil($curpage - floor($this->limit / 2)));
        if ($startpage < 1) {
            $startpage = 1;
        }
        $endpage = $startpage + $this->num_page - 1;
        $lastpage = ceil($this->total_record / $this->limit);


        if ($endpage > $lastpage) {
            $endpage = $lastpage;
        }
        $paginationObject->text = "";
        $paginationObject->ref = "";
        $paginationObject->ref_first = 0;
        $paginationObject->str_first = $this->str_first;
        $paginationObject->str_prev = $this->str_prev;
        $paginationObject->ref_prev = ($curpage - 2) * $this->limit;
        if ($paginationObject->ref_prev < 0) {
            $paginationObject->ref_prev = 0;
        }
        $paginationObject->str_next = $this->str_next;
        $paginationObject->ref_next = ($curpage) * $this->limit;
        $paginationObject->str_last = $this->str_last;
        $paginationObject->ref_last = floor($this->total_record / $this->limit) * $this->limit;
        if ($paginationObject->ref_last == $this->total_record) {
            $paginationObject->ref_last = $this->total_record - $this->limit;
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
            $result[$j]->ref = str_replace("?&", "?", $arg . self::AMPERSAND_OFFSET . (($i - 1) * $this->limit));
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
        $this->result = $result;
    }

    public function buildHTML()
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