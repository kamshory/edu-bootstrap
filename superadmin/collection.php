<?php
require_once dirname(dirname(__FILE__)) . "/lib.inc/auth-admin.php";
if ($adminLoggedIn->admin_level != 1) {
    require_once dirname(__FILE__) . "/bukan-super-admin.php";
    exit();
}
require_once dirname(dirname(__FILE__)) . "/lib.inc/lib.test.php";

$pageTitle = "Kelola Paket Soal";
$pagination = new \Pico\PicoPagination();
$time_create = $time_edit = $database->getLocalDateTime();



$sql = "SELECT * FROM `edu_test_collection` WHERE `active` = true ";
$stmt = $database->executeQuery($sql);
if ($stmt->rowCount() > 0) {

    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows as $data3) {
        $basename = $data3['file_path'];
        $file_path = dirname(dirname(__FILE__)) . "/media.edu/question-collection/data/" . $basename;

        $s = file_get_contents($file_path);
        $test_data = simplexml_load_string($s);



        $files = array();
        $questions = array();
        $options = array();

        $data3['collection'] = count($test_data->item);

        $sort_order = 0;
        $nquestion = 0;
        $noption = 0;
        $question_count = $nquestion = count($test_data->item);


        $number_of_option = kh_filter_input(INPUT_POST, "number_of_option", FILTER_SANITIZE_NUMBER_UINT);
        $numbering = kh_filter_input(INPUT_POST, "numbering", FILTER_SANITIZE_STRING_NEW);
        $competence = trim(kh_filter_input(INPUT_POST, "basic_competence", FILTER_SANITIZE_STRING_NEW));
        $random = kh_filter_input(INPUT_POST, "random", FILTER_SANITIZE_NUMBER_UINT);
        $question_text = kh_filter_input(INPUT_POST, "question");
        $question_text = utf8ToEntities($question_text);
        $question_text = htmlspecialchars($question_text);

        for ($question_index = 0; $question_index < $question_count; $question_index++) {
            $xml_item = "";
            if ($question_index >= $nquestion || @$_GET['option'] == 'add') {
                $question_index = $nquestion;
            }
            if ($question_index == $nquestion) {
                $data = array('text' => '', 'numbering' => 'upper-alpha', 'random' => '1');
                $data_options = array();
                for ($x = 0; $x < $noption; $x++) {
                    $data_options[$x] = array('text' => '', 'value' => '0');
                }
            } else {
                $question_data = $test_data->item[$question_index];
                $data = $question_data->question;
                $data_options = $question_data->answer->option;
            }

            if (isset($data->file)) {
                $content = "";
                if (isset($data->file[0])) {
                    foreach ($data->file as $key => $val) {
                        $name = $val->name;
                        $type = $val->type;
                        $encoding = $val->encoding;
                        $search = ' src="' . $name . '"';
                        $replace = 'data:' . $type . ';' . $encoding . ',' . $val->data;
                        $data->text = str_ireplace($search, ' src="' . $replace . $content . '"', $data->text);
                    }
                } else if (isset($data->file->name)) {
                    $name = $data->file->name;
                    $type = $data->file->type;
                    $encoding = $data->file->encoding;
                    $search = ' src="' . $name . '"';
                    $replace = 'data:' . $type . ';' . $encoding . ',' . $data->file->data;
                    $data->text = str_ireplace($search, ' src="' . $replace . $content . '"', $data->text);
                }
            }


            if (!isset($data3['standard_score'])) {
                $data3['standard_score'] = 1;
            }

            $numbering = $picoEdu->trimWhitespace($data->numbering);
            $i = 0;
            $count_option = count($data_options);

            for ($jj = 0; $jj < $count_option; $jj++) {
                $data2 = $data_options[$jj];
                if (@$data2->value > 0 && @$data2->value > $data3['standard_score']) {
                    $data3['standard_score'] = $data2->value;
                }
                if (@$data2->score > 0 && @$data2->score > $data3['standard_score']) {
                    $data3['standard_score'] = $data2->score;
                }
            }


            for ($jj = 0; $jj < $count_option; $jj++) {
                $data2 = $data_options[$jj];
                if (isset($data2->file)) {
                    if (isset($data2->file[0])) {
                        foreach ($data2->file as $key => $val) {
                            $name = $val->name;
                            $type = $val->type;
                            $encoding = $val->encoding;
                            $search = ' src="' . $name . '"';
                            $replace = 'data:' . $type . ';' . $encoding . ',' . $val->data;
                            $data2->text = str_ireplace($search, ' src="' . $replace . $content . '"', $data2->text);
                        }
                    } else if (isset($data2->file->name)) {
                        $name = $data2->file->name;
                        $type = $data2->file->type;
                        $encoding = $data2->file->encoding;
                        $search = ' src="' . $name . '"';
                        $replace = 'data:' . $type . ';' . $encoding . ',' . $data2->file->data;
                        $data2->text = str_ireplace($search, ' src="' . $replace . $content . '"', $data2->text);
                    }
                }

                $i++;
            }
        }
    }
}
