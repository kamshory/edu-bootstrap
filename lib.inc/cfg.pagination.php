<?php
class PicoPagination
{

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
    
        $this->limit_sql = " limit ".$this->offset.",".$this->limit;
        $this->str_result = "";          
    }

 
}



$pagination = new \PicoPagination();