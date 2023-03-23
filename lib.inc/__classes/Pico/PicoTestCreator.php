<?php

namespace Pico;

class PicoTestCreator
{
    public static $numberingList = array(
        'upper-alpha' => array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'),
        'lower-alpha' => array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'),
        'upper-roman' => array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'),
        'lower-roman' => array('i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'),
        'decimal' => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10'),
        'decimal-leading-zero' => array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10')
    );
    public function loadXmlData_word($xml_file, $key = 0) //NOSONAR
    {
        $s = file_get_contents($xml_file);
        $test_data = simplexml_load_string($s);
        $files = array();
        $questions = array();
        $options = array();
        $sort_order = 0;
        $index = 0;
        $answer_key = '';

        $answer = \Pico\PicoTestCreator::$numberingList;

        foreach ($test_data->item as $question) {
            // petanyaan
            $text_pertanyaan = trim(@$question->question->text);
            $random = ((int) @$question->question->random); //NOSONAR
            $numbering = addslashes(trim(@$question->question->numbering)); //NOSONAR
            $competence = addslashes(trim(@$question->question->competence)); //NOSONAR
            $sort_order++;
            if (count(@$question->question->file)) {
                foreach ($question->question->file as $file) {
                    $name_file = trimWhitespace(@$file->name);
                    $type_file = trimWhitespace(@$file->type);
                    $encoding_file = trimWhitespace(@$file->encoding);
                    $data_file = trimWhitespace(@$file->data);
                    $files[$name_file] = array('type' => $type_file, 'encoding' => $encoding_file, 'data' => $data_file);
                }
            }
            $pertanyaan = $text_pertanyaan;

            if (count(@$question->answer->option) > 0) {
                $options = array();
                $answer_key = '';
                $option_index = 0;
                foreach ($question->answer->option as $index_option => $option) {
                    $text_option = trim(@$option->text);
                    $score = trim(@$option->value) * 1;
                    if (count(@$option->file)) {
                        foreach ($option->file as $file) {
                            $name_file = trimWhitespace(@$file->name);
                            $type_file = trimWhitespace(@$file->type);
                            $encoding_file = trimWhitespace(@$file->encoding);
                            $data_file = trimWhitespace(@$file->data);
                            $files[$name_file] = array('type' => $type_file, 'encoding' => $encoding_file, 'data' => $data_file);
                        }
                    }
                    $option = \Pico\PicoDOM::removeParagraphTag($text_option);

                    $sort_order = ((int) $index_option) + 1;
                    if ($score > 0) {
                        if ($answer_key == '') {
                            $answer_key = @$answer[$numbering][$option_index];
                        }
                        $cs = ' option-circle-selected'; //NOSONAR
                    } else {
                        $cs = '';
                    }
                    $options[] = "<div class=\"option-item\" style=\"text-indent:-24px;padding:3px 0 3px 24px;\"><span style=\"text-indent:0px;display:inline-block;width:24px;\">" . ltrim(@$answer[$numbering][$option_index] . ". ", ".") . "</span>" . $option . "</div>";
                    $option_index++;
                }
            }
            $questions[] = "<li data-question-index=\"$index\">" . $pertanyaan . "<div class=\"option-group\">" . implode("\r\n", $options) . "</div>" . ($key ? ("<div>Jawaban: $answer_key</div>") : "") . "</li>";
            $index++;
        }
        $text_all = "<ol class=\"test-question\">" . implode("\r\n", $questions) . "</ol>";
        foreach ($files as $name => $data) {
            $text_all = str_replace(' src="' . $name . '"', ' src="data:' . $data['type'] . ';' . $data['encoding'] . ',' . $data['data'] . '"', $text_all);
        }
        return $text_all;
    }

    public function loadXmlData($xml_file) //NOSONAR
    {
        $s = file_get_contents($xml_file);
        $test_data = simplexml_load_string($s);
        $files = array();
        $questions = array();
        $options = array();
        $sort_order = 0;
        $index = 0;
        foreach ($test_data->item as $question) {
            // petanyaan
            $text_pertanyaan = trim(@$question->question->text);
            $numbering = addslashes(trim(@$question->question->numbering));
            $competence = addslashes(trim(@$question->question->competence));
            $sort_order++;
            if (count(@$question->question->file)) {
                foreach ($question->question->file as $file) {
                    $name_file = trimWhitespace(@$file->name);
                    $type_file = trimWhitespace(@$file->type);
                    $encoding_file = trimWhitespace(@$file->encoding);
                    $data_file = trimWhitespace(@$file->data);
                    $files[$name_file] = array('type' => $type_file, 'encoding' => $encoding_file, 'data' => $data_file);
                }
            }
            $pertanyaan = $text_pertanyaan;

            if (count(@$question->answer->option) > 0) {
                $options = array();
                foreach ($question->answer->option as $index_option => $option) {
                    $text_option = trim(@$option->text);
                    $score = trim(@$option->value) * 1;
                    if (count(@$option->file)) {
                        foreach ($option->file as $file) {
                            $name_file = trimWhitespace(@$file->name);
                            $type_file = trimWhitespace(@$file->type);
                            $encoding_file = trimWhitespace(@$file->encoding);
                            $data_file = trimWhitespace(@$file->data);
                            $files[$name_file] = array('type' => $type_file, 'encoding' => $encoding_file, 'data' => $data_file);
                        }
                    }
                    $option = $text_option;

                    $sort_order = ((int)$index_option) + 1;
                    if ($score > 0) {
                        $cs = ' option-circle-selected';
                    } else {
                        $cs = '';
                    }
                    $options[] = "<li class=\"list-option\" ><span class=\"option-circle$cs\">$score</span><div class=\"list-option-item\"><div class=\"option-content\">" . $option . "</div></div></li>";
                }
            }
            $questions[] = "<li class=\"list-question\" data-question-index=\"$index\"><div class=\"question\"><span class=\"competence-control\">" . $competence . "</span><a class=\"select-question\" href=\"javascript:;\" data-index=\"$index\" data-selected=\"true\"><span></span></a>" . $pertanyaan . "<ol style=\"list-style-type:$numbering\">" . implode("\r\n", $options) . "</ol></div></li>";
            $index++;
        }
        $text_all = "<ol class=\"test-question\">" . implode("\r\n", $questions) . "</ol>";
        foreach ($files as $name => $data) {
            $text_all = str_replace(' src="' . $name . '"', ' src="data:' . $data['type'] . ';' . $data['encoding'] . ',' . $data['data'] . '"', $text_all);
        }
        return $text_all;
    }
    public function replaceImageData($html, $base_dir) //NOSONAR
    {
        global $cfg;
        error_reporting(0);
        $files = array();
        $dom = new \DomDocument();
        $html = utf8ToEntities($html);
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $dom->preserveWhiteSpace = false;
        $images = $dom->getElementsByTagName('img');
        $obj = new \stdClass();
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            $skip = false;
            if (is_array($cfg->image_not_exported)) {
                foreach ($cfg->image_not_exported as $val) {
                    if (stripos($src, $val) !== false) {
                        $skip = true;
                    }
                }
            }
            if ($skip) {
                continue;
            }

            if (stripos($src, "data:") === 0) //NOSONAR
            {
                $arr = explode(",", $src, 2);
                $arr2 = explode(";", $arr[0]);
                $arr3 = explode(":", $arr2[0]);
                $content_type = $arr3[1];
                $arr4 = explode("/", $arr3[1]);
                $base_name = md5($arr[1]) . "." . $arr4[1];
                $data = base64_decode($arr[1]);
            } else {
                if (stripos($src, "://") === false) {
                    $path = $base_dir . $src;
                    $data = file_get_contents($path);
                } else {
                    $url = $src;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //NOSONAR
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11'); //NOSONAR
                    $data = curl_exec($ch);
                    $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE); //NOSONAR
                    curl_close($ch);
                }
                $hash = substr(md5($src), 0, 6) . "_";

                if (stripos($src, "?", 0) !== false) {
                    $bn = explode("?", $src);
                    $src = $bn[0];
                }
                if (stripos($src, "#", 0) !== false) {
                    $bn = explode("#", $src);
                    $src = $bn[0];
                }

                $base_name = $hash . basename($src);
                $ext = pathinfo($src, PATHINFO_EXTENSION);

                $image->setAttribute('src', $base_name);

                switch ($ext) {
                    case 'gif':
                        $content_type = "image/gif"; //NOSONAR
                        break;

                    case 'png':
                        $content_type = "image/png"; //NOSONAR
                        break;

                    case 'jpeg':
                    case 'jpg':
                        $content_type = "image/jpeg"; //NOSONAR
                        break;

                    default:
                        $content_type = "image/jpeg"; //NOSONAR
                }
            }
            unset($obj);
            $obj = new \stdClass();
            $obj->name = $base_name;
            $obj->type = $content_type;
            $obj->encoding = 'base64';
            $obj->data = base64_encode($data);
            $files[] = $obj;
        }

        $dom->encoding = "utf-8";
        $data = $dom->saveHTML();
        $data = utf8ToEntities($data);
        if (stripos($data, '<body') !== false) {
            $data = \Pico\PicoDOM::getDataInTag($data, 'body');
        } else if (stripos($data, '<head') !== false) {
            $data = \Pico\PicoDOM::getDataInTag($data, 'head');
        }
        unset($obj);
        $obj = new \stdClass();
        $obj->html = $data;
        $obj->files = $files;
        return $obj;
    }
    public function extractImage($html, $base_dir) //NOSONAR
    {
        global $cfg;
        $files = array();
        $dom = new \DomDocument();
        $html = utf8ToEntities($html);
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $dom->preserveWhiteSpace = false;
        $images = $dom->getElementsByTagName('img');
        $obj = new \stdClass();
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            $skip = false;
            if (is_array($cfg->image_not_exported)) {
                foreach ($cfg->image_not_exported as $val) {
                    if (stripos($src, $val) !== false) {
                        $skip = true;
                    }
                }
            }
            if ($skip) {
                continue;
            }
            if (stripos($src, "data:") === 0) {
                $arr = explode(",", $src, 2);
                $arr2 = explode(";", $arr[0]);
                $arr3 = explode(":", $arr2[0]);
                $content_type = $arr3[1];
                $arr4 = explode("/", $arr3[1]);
                $base_name = md5($arr[1]) . "." . $arr4[1];
                $data = base64_decode($arr[1]);
                $image->setAttribute('src', $base_name);
            } else {
                if (stripos($src, "://") === false) {
                    $path = $base_dir . $src;
                    $data = file_get_contents($path);
                } else {
                    $url = $src;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //NOSONAR
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11'); //NOSONAR
                    $data = curl_exec($ch);
                    $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE); //NOSONAR
                    curl_close($ch);
                }
                $hash = substr(md5($src), 0, 6) . "_";

                if (stripos($src, "?", 0) !== false) {
                    $bn = explode("?", $src);
                    $src = $bn[0];
                }
                if (stripos($src, "#", 0) !== false) {
                    $bn = explode("#", $src);
                    $src = $bn[0];
                }

                $base_name = $hash . basename($src);
                $ext = pathinfo($src, PATHINFO_EXTENSION);

                $image->setAttribute('src', $base_name);

                switch ($ext) {
                    case 'gif':
                        $content_type = "image/gif";
                        break;

                    case 'png':
                        $content_type = "image/png";
                        break;

                    case 'jpeg':
                    case 'jpg':
                        $content_type = "image/jpeg";
                        break;

                    default:
                        $content_type = "image/jpeg";
                }
            }
            unset($obj);
            $obj = new \stdClass();
            $obj->name = $base_name;
            $obj->type = $content_type;
            $obj->encoding = 'base64';
            $obj->data = base64_encode($data);
            $files[] = $obj;
        }

        $audios = $dom->getElementsByTagName('audio');
        $obj = new \stdClass();
        foreach ($audios as $audio) {
            $src = $audio->getAttribute('src');
            $skip = false;
            if (is_array($cfg->audio_not_exported)) {
                foreach ($cfg->audio_not_exported as $val) {
                    if (stripos($src, $val) !== false) {
                        $skip = true;
                    }
                }
            }
            if ($skip) {
                continue;
            }

            if (stripos($src, "data:") === 0) {
                $arr = explode(",", $src, 2);
                $arr2 = explode(";", $arr[0]);
                $arr3 = explode(":", $arr2[0]);
                $content_type = $arr3[1];
                $arr4 = explode("/", $arr3[1]);
                $base_name = md5($arr[1]) . "." . $arr4[1];
                $data = base64_decode($arr[1]);
                $audio->setAttribute('src', $base_name);
            } else {
                if (stripos($src, "://") === false) {
                    $path = $base_dir . $src;
                    $data = file_get_contents($path);
                } else {
                    $url = $src;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //NOSONAR
                    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11'); //NOSONAR
                    $data = curl_exec($ch);
                    $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                }
                $hash = substr(md5($src), 0, 6) . "_";

                if (stripos($src, "?", 0) !== false) {
                    $bn = explode("?", $src);
                    $src = $bn[0];
                }
                if (stripos($src, "#", 0) !== false) {
                    $bn = explode("#", $src);
                    $src = $bn[0];
                }

                $base_name = $hash . basename($src);
                $ext = pathinfo($src, PATHINFO_EXTENSION);

                $audio->setAttribute('src', $base_name);

                switch ($ext) {
                    case 'ogg':
                        $content_type = "audio/ogg";
                        break;

                    case 'mp4':
                        $content_type = "audio/mp4";
                        break;

                    case 'mp3':
                        $content_type = "audio/mp3";
                        break;

                    case 'mpeg':
                        $content_type = "audio/mpeg";
                        break;

                    case 'wav':
                        $content_type = "audio/wav";
                        break;

                    default:
                        $content_type = "audio/mp3";
                }
            }
            unset($obj);
            $obj = new \stdClass();

            $obj->name = $base_name;
            $obj->type = $content_type;
            $obj->encoding = 'base64';
            $obj->data = base64_encode($data);

            $files[] = $obj;
        }

        $dom->encoding = "utf-8";
        $data = $dom->saveHTML();
        $data = utf8ToEntities($data);
        if (stripos($data, '<body') !== false) {
            $data = \Pico\PicoDOM::getDataInTag($data, 'body');
        } else if (stripos($data, '<head') !== false) {
            $data = \Pico\PicoDOM::getDataInTag($data, 'head');
        }

        unset($obj);
        $obj = new \stdClass();

        $obj->html = $data;
        $obj->files = $files;

        return $obj;
    }

    public function exportQuestion($database, $question_id, $base_dir = "") //NOSONAR
    {
        global $arr_files;
        $sql = "SELECT * FROM `edu_question` WHERE `question_id` = '$question_id' ";
        $stmt = $database->executeQuery($sql);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        $content = $data['content'];
        $numbering = $data['numbering'];
        $random = ((int) $data['random']);
        $competence = trim($data['basic_competence']);

        $html_question = "";
        $html_option = "";
        $file1 = "";
        $file2 = "";
        if ($content != "") {
            $parsed_data = $this->extractImage($content, $base_dir);
            $files = $parsed_data->files;
            $content = htmlspecialchars($parsed_data->html);

            if (count($files) > 0) {
                foreach ($files as $val) {
                    if (!in_array($val->name, $arr_files)) {
                        $file1 .= "
            <file>
            <name>" . $val->name . "</name>
            <type>" . $val->type . "</type>
            <encoding>" . $val->encoding . "</encoding>
            <data>" . $val->data . "</data>
            </file>\r\n";
                        $arr_files[] = $val->name;
                    }
                }
            }
        }

        $html_question = "
        <question>
        <text>" . ($content) . "</text>
        <random>$random</random>
        <numbering>$numbering</numbering>
        <competence>$competence</competence>
        $file1
        </question>\r\n";


        $sql = "SELECT * FROM `edu_option` WHERE `question_id` = '$question_id' ORDER BY `sort_order` ASC ";
        $stmt = $database->executeQuery($sql);
        $html_option .= "
            <answer>
            ";
        if($stmt->rowCount() > 0) {
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $data) {
                $content = $data['content'];
                $score = $data['score'] * 1;

                $file2 = "";
                if ($content != '') {
                    $parsed_data = $this->extractImage($content, $base_dir);
                    $files = $parsed_data->files;
                    $content = htmlspecialchars($parsed_data->html);
                    if (count($files) > 0) {
                        foreach ($files as $val) {
                            if (!in_array($val->name, $arr_files)) {
                                $file2 .= "
                    <file>
                    <name>" . $val->name . "</name>
                    <type>" . $val->type . "</type>
                    <encoding>" . $val->encoding . "</encoding>
                    <data>" . $val->data . "</data>
                    </file>\r\n";
                            }
                            $arr_files[] = $val->name;
                        }
                    }
                }
                $html_option .= "
                <option>
                    <text>" . ($content) . "</text>
                    <value>$score</value>
                    <score>$score</score>
                    $file2
                </option>\r\n";
            }
        }
        $html_option .= "
            </answer>\r\n";

        return $html_question . $html_option;
    }

    public function exportTest($database, $test_id, $base_dir = "")
    {
        $html = "<" . "?xml version=\"1.0\" encoding=\"utf-8\"?" . ">
    <test>
    ";
        $sql = "SELECT `question_id` FROM `edu_question` WHERE `test_id` = '$test_id' ORDER BY `sort_order` ASC, `question_id` ASC ";
        $stmt = $database->executeQuery($sql);
        if($stmt->rowCount() > 0) {
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $data) {
                $question = $this->exportQuestion($database, $data['question_id'], $base_dir);
                $html .= "
        <item>" . $question . "</item>\r\n";
            }
        }
        $html .= "</test>";
        return $html;
    }

    public function parseRawQuestion($raw_text)
    {
        $raw_data = explode("\n", $raw_text);
        foreach ($raw_data as $line_no => $line_text) {
            $raw_data[$line_no] = trim($line_text, " \t\r\n ");
        }
        $text_data = trim(implode("\r\n", $raw_data), " \t\r\n ");
        while (stripos($text_data, "\r\n\r\n\r\n") !== false) {
            $text_data = str_replace("\r\n\r\n\r\n", "\r\n\r\n", $text_data);
        }
        $text_data = str_replace("\\\\\r\n", "<br>", $text_data);
        return explode("\r\n\r\n", $text_data);
    }

    public function optionMatch($opt, $numbering)
    {
        $numberingList = self::$numberingList;
        if (!isset($numberingList[$numbering])) {
            return -1;
        }
        $num = $numberingList[$numbering];
        foreach ($num as $k => $v) {
            if ($v == $opt) {
                return $k;
            }
        }
        return -1;
    }

    public function getNumType($lines)
    {
        $numberingList = \Pico\PicoTestCreator::$numberingList;;
        foreach ($numberingList as $type) {
            $arrType = array();
            $lastLine = -1;
            foreach ($lines as $key2 => $line) {
                if (stripos($line, ".") !== false) {
                    $arr = explode('.', trim($line), 2);
                    $tp = trim($arr[0]);
                    if (in_array($tp, $type) && $key2 > $lastLine) {
                        $arrType[] = $tp;
                    }
                }
            }
            $numbering = $this->matchNumberingType($arrType, $numberingList);
            if ($numbering !== false) {
                return $numbering;
            }
        }
        return '';
    }
    public function matchNumberingType($arrType, $numberingList)
    {
        foreach ($numberingList as $key1 => $type) {
            if (count($arrType) > 1 && $type[0] == $arrType[0] && $type[1] == $arrType[1]) {
                return $key1;
            }
        }
        return false;
    }

    public function parseQuestion($question) //NOSONAR
    {
        $whiteSpaceTrimmer = " \t\r\n\t ";
        $question_text = "";
        $question = str_replace("\\\\\r\n", "<br />", $question); //NOSONAR
        $lines = explode("\r\n", $question);

        $question_text = $lines[0];
        $numbering_type = false;
        $result = array();
        $options = array();

        $lineslength = count($lines);
        if ($lineslength > 2) {
            $numbering_type = $this->getNumType($lines);

            $k = -1;
            for ($i = 1; $i < $lineslength - 1; $i++) {
                if (stripos($lines[$i], '.') !== false) {
                    $tmp = explode(".", $lines[$i], 2);
                    $opt = trim($tmp[0], $whiteSpaceTrimmer);
                    if ($this->optionMatch($opt, $numbering_type) > -1) {
                        $texx = $tmp[1];
                        $options[] = array(
                            'text' => trim($texx, $whiteSpaceTrimmer),
                            'value' => 0,
                            'score' => 0
                        );
                        $k++;
                    } else {
                        if ($k == -1) {
                            $question_text .= '<br />' . $lines[$i];
                        } else {
                            $options[$k]['text'] .= '<br />' . $lines[$i];
                        }
                    }
                } else {
                    if ($k == -1) {
                        $question_text .= '<br />' . $lines[$i];
                    } else {
                        $options[$k]['text'] .= '<br />' . $lines[$i];
                    }
                }
            }
            if ($lineslength > 3) {
                $lastIsAnswer = true;
                if (substr_count($lines[$lineslength - 1], "\\\\:") == substr_count($lines[$lineslength - 1], ":")) //NOSONAR
                {
                    $lastIsAnswer = false;
                }
                if (stripos($lines[$lineslength - 1], ':') !== false && $lastIsAnswer) {
                    $lines[$lineslength - 1] = str_replace("\t", " ", $lines[$lineslength - 1]);
                    $tmp = explode(":", $lines[$lineslength - 1], 2);
                    $opt = trim($tmp[1], $whiteSpaceTrimmer);
                    $xx = explode(" ", $opt);
                    $opt = $xx[0];
                    $opt = trim($opt, $whiteSpaceTrimmer);
                    $answerIndex = $this->optionMatch($opt, $numbering_type);
                    if ($answerIndex > -1 && $answerIndex < count($options)) {
                        $options[$answerIndex]['value'] = 1;
                        $options[$answerIndex]['score'] = 1;
                    } else {
                        $tmp = explode(".", $lines[$lineslength - 1], 2);
                        $opt = trim($tmp[0], $whiteSpaceTrimmer);
                        if ($this->optionMatch($opt, $numbering_type) > -1) {
                            $texx = $tmp[1];

                            $options[] = array(
                                'text' > trim($texx, $whiteSpaceTrimmer),
                                'value' => 0,
                                'score' => 0
                            );
                        }
                    }
                } else {
                    if (stripos($lines[$lineslength - 1], '.') !== false) {
                        $tmp = explode(".", $lines[$lineslength - 1], 2);
                        $opt = trim($tmp[0], $whiteSpaceTrimmer);
                        if ($this->optionMatch($opt, $numbering_type) > -1) {
                            $texx = $tmp[1];
                            $options[] = array(
                                'text' => trim($texx, $whiteSpaceTrimmer),
                                'value' => 0,
                                'score' => 0
                            );
                        }
                    }
                }
            } else {
                $question_text = $lines[0];
                $options = array();
            }
            $question_text = trim($question_text, " \r\n ");
            $question_text = str_replace("\\\\:", ":", $question_text);
            foreach ($options as $key => $val) {
                $options[$key]['text'] = $this->detectTable($options[$key]['text']);
                $options[$key]['text'] = str_replace("\\\\:", ":", $options[$key]['text']);
            }
            $question_text = $this->detectTable($question_text);

            $result = array(
                'question' => $question_text,
                'numbering' => $numbering_type,
                'option' => $options
            );
        }
        return $result;
    }

    public function fixTable($html)
    {
        $html = str_replace('&lt;table border=&quot;1&quot;&gt;', '<table border="1">', $html);
        $html = str_replace('&lt;table border="1"&gt;', '<table border="1">', $html);
        $html = str_replace('&lt;table&gt;', '<table>', $html);
        $html = str_replace('&lt;/table&gt;', '</table>', $html);
        $html = str_replace('&lt;thead&gt;', '<thead>', $html);
        $html = str_replace('&lt;/thead&gt;', '</thead>', $html);
        $html = str_replace('&lt;tbody&gt;', '<tbody>', $html);
        $html = str_replace('&lt;/tbody&gt;', '</tbody>', $html);
        $html = str_replace('&lt;tr&gt;', '<tr>', $html);
        $html = str_replace('&lt;/tr&gt;', '</tr>', $html);
        $html = str_replace('&lt;td&gt;', '<td>', $html);
        $html = str_replace('&lt;/td&gt;', '</td>', $html); //NOSONAR
        return $html;
    }


    public function createLineObject($lineNumber, $lineContent)
    {
        $nPipe = count(explode('|', $lineContent)) - 1;
        $x1 = preg_replace("/[^-\|]/", '', $lineContent);
        $x2 = preg_replace("/[^-\|]/", '', $lineContent) . "\\";
        $x4 = preg_replace("/\s/", '', $lineContent);

        $hasPipeAndDash =
            ($x1 == $x4 && strlen($x4) > 1)
            ||
            ($x2 == $x4 && strlen($x4) > 1);
        return array(
            'lineNumber' => ((int) $lineNumber),
            'content' => $lineContent,
            'pipe' => $nPipe,
            'pipeDash' => $hasPipeAndDash,
            'startTable' => false,
            'inTable' => false,
            'endTable' => false
        );
    }

    public function detectTable($html) //NOSONAR
    {
        $html2 = $html;
        $arr = explode("<br />", $html2);
        $arr2 = explode("<br />", $html2);
        $lineObj = array();
        foreach ($arr as $i => $val) {
            $arr2[$i] = trim($val);
            $lineObj[$i] = $this->createLineObject($i, $arr2[$i]);
        }
        $inTable = false;
        $tableObj = array();
        $j = 0;
        for ($i = 1; $i < count($lineObj); $i++) {
            if ($lineObj[$i]['pipeDash'] && $lineObj[$i - 1]['pipe'] > 0) {
                $inTable = true;
                $lineObj[$i]['inTable'] = true;
                $lineObj[$i - 1]['startTable'] = true;
                $tableObj[$j] = array();
                $tableObj[$j][] = $lineObj[$i - 1];
                $tableObj[$j][] = $lineObj[$i];
            }
            if ($inTable && !$lineObj[$i]['pipeDash'] && $lineObj[$i]['pipe'] > 0) {
                $lineObj[$i]['inTable'] = true;
                $lineObj[$i]['startTable'] = false;
                if ($i == count($lineObj) - 1) {
                    $lineObj[$i]['endTable'] = true;
                }
                $tableObj[$j][] = $lineObj[$i];
            }
            if ($inTable && $lineObj[$i]['pipe'] == 0) {
                $inTable = false;
                $lineObj[$i - 1]['endTable'] = true;
                $lineObj[$i - 1]['startTable'] = false;
                $tableObj[$j][$i - 2]['endTable'] = true;
                $tableObj[$j][$i - 2]['startTable'] = false;
                $tableObj[$j][$i - 2]['lineNumber'] = $lineObj[$i - 1]['lineNumber'];
                $tableObj[$j][$i - 2]['inTable'] = $lineObj[$i - 1]['inTable'];
                $tableObj[$j][$i - 2]['pipeDash'] = $lineObj[$i - 1]['pipeDash'];
                $tableObj[$j][$i - 2]['content'] = $lineObj[$i - 1]['content'];
                $j++;
            }
        }

        foreach ($arr as $i => $val) {
            $arr[$i] = $val . "<br />";
        }

        foreach ($tableObj as $j => $val) {
            $tab = $val;
            foreach ($tab as $i => $val2) {
                $content = '';
                if ($tab[$i]['startTable']) {
                    $content = $this->createTableHeader($tab[$i]['content']);
                } else if ($tab[$i]['inTable']) {
                    if ($tab[$i]['pipeDash']) {
                        $content = '';
                    } else if ($tab[$i]['endTable']) {
                        $content = $this->createTableContent($tab[$i]['content']) . '</tbody></table>';
                    } else {
                        $content = $this->createTableContent($tab[$i]['content']);
                    }
                }
                $arr[$tab[$i]['lineNumber']] = $content;
            }
        }
        return implode('', $arr);
    }

    public function createTableHeader($input)
    {
        $input = trim($input);
        $arr = explode('|', $input);

        $content = '<table border="1"><thead><tr>';
        for ($i = 0; $i < count($arr); $i++) {
            if (
                ($i == 0 && $arr[$i] != '')
                ||
                ($i == count($arr) - 1 && $arr[$i] != '' && $arr[$i] != '\\')
                ||
                $arr[$i] != ''
            ) {
                // start with |
                if ($i == count($arr) - 1 && trim($arr[$i]) == '\\') {
                    // Do nothing
                } else {
                    $content .= '<td>' . $arr[$i] . '</td>';
                }
            }
        }
        $content .= '</tr></thead><tbody>';
        return $content;
    }
    public function createTableContent($input)
    {
        $input = trim($input);
        $arr = explode('|', $input);
        $content = '<tr>';
        for ($i = 0; $i < count($arr); $i++) {
            if (
                ($i == 0 && $arr[$i] != '')
                ||
                ($i == count($arr) - 1 && trim($arr[$i]) != '' && trim($arr[$i]) != '\\')
                ||
                $arr[$i] != ''
            ) {
                if ($i == count($arr) - 1 && trim($arr[$i]) == '\\') {
                    // Do nothing
                } else {
                    $content .= '<td>' . $arr[$i] . '</td>';
                }
            }
        }
        $content .= '</tr>';
        return $content;
    }

    public function customRTrim($line, $sub)
    {
        if (stripos($line, $sub) !== false && substr($line, strlen($line) - count($sub)) == $sub) {
            return substr($line, 0, strlen($line) - strlen($sub));
        }
        return $line;
    }
}
