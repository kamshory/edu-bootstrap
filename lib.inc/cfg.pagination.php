<?php
$pagination = new StdClass();
$pagination->query = trim(kh_filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS));
$pagination->query_edit = kh_filter_input(INPUT_GET, 'q');
$pagination->query_edit = trim(htmlspecialchars($pagination->query_edit));
$pagination->offset = kh_filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_NUMBER_UINT);

$cfg->result_per_page = 20;
$pagination->limit = 20;
$pagination->num_page = 4;
$pagination->str_first = '&laquo;';//'<span class="pagination-icon first"></span>';//$lang_pack['pagination_first'];
$pagination->str_last = '&raquo;';//'<span class="pagination-icon last"></span>';//$lang_pack['pagination_last'];
$pagination->str_prev = '&lsaquo;';//'<span class="pagination-icon prev"></span>';//$lang_pack['pagination_prev'];
$pagination->str_next = '&rsaquo;';//'<span class="pagination-icon next"></span>';//$lang_pack['pagination_next'];
$pagination->str_noresult = 'Pencarian tidak menemukan hasil.';//$lang_pack['pagination_noresult'];
$pagination->str_nodata = 'Data tidak ditemukan.';//$lang_pack['pagination_nodata'];
$pagination->str_nodata_add = 'Data tidak ditemukan. <a href="%s">Klik di sini untuk membuat baru</a>.';//$lang_pack['pagination_nodata_add'];
$pagination->str_keyword = 'Kata kunci';//$lang_pack['search_label'];
$pagination->str_search = 'Cari';//$lang_pack['search_button_search'];
$pagination->str_soundlike = 'Cari seperti';//$lang_pack['search_button_soundlike'];
$pagination->str_record = 'Baris';//$lang_pack['pagination_record'];
$pagination->str_from = 'dari';//$lang_pack['pagination_from'];
$pagination->str_to = 'hingga';//$lang_pack['pagination_to'];
$pagination->str_of = 'dari';//$lang_pack['pagination_of'];
$pagination->limit_sql = " limit ".$pagination->offset.",".$pagination->limit;
$pagination->str_result = "";

$cfg->dec_precision = 2;//getProfile('dec_precision', $cfg->dec_precision);
$cfg->dec_separator = ".";//getProfile('dec_separator', $cfg->dec_separator);
$cfg->dec_thousands_separator = ",";//getProfile('dec_thousands_separator', $cfg->dec_thousands_separator);


$pagination->array_get = array();

$cfg->dec_precision = 2;
$cfg->dec_separator = ".";
$cfg->dec_thousands_separator = ",";
