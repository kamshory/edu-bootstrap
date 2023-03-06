<?php

namespace Pico;

class PicoTest
{
    private $database;
    private $message = "";

    /**
     * Constructor of PicoTest
     * @param \Pico\PicoDatabase $database
     */
    public function __construct($database)
    {
        $this->database = $database;
    }

    /**
     * Eligible
     * @param \Pico\AuthStudent
     * @param \Pico\EduTest $test
     * @param string $token
     * @return bool
     * @throws \Pico\PicoTestException
     */
    public function eligible($student, $test, $token = "") //NOSONAR
    {
        $eligible = false;
        if (empty($student->student_id)) {
            throw new \Pico\PicoTestException("Anda harus masuk sebagai siswa", \Pico\PicoTestException::LOGIN_REQUIRED);
        }
        if ($test->open) {
            $eligible = true;
        } else {
            if (stripos($test->class, $student->class_id) !== false) {
                $eligible = true;
            } else {
                throw new \Pico\PicoTestException("Ujian ini bukan untuk Anda", \Pico\PicoTestException::TEST_NOT_FOR_YOU);
            }
        }
        if ($eligible) {
            $now = time();
            if ($test->test_availability == 'L' && (strtotime($test->available_from) > $now || strtotime($test->available_to) < $now)) {
                $eligible = false;
                throw new \Pico\PicoTestException("Ujian ini tersedia antara " . $test->available_from . " hingga " . $test->available_to, \Pico\PicoTestException::TEST_NOT_IN_PERIOD);
            }
        }
        if ($eligible && $student->use_token) {
            if (!empty($token)) {
                $tokenObj = $this->getToken($token, $test->test_id, $student->student_id);
                if (empty($tokenObj->token_id)) {
                    $eligible = false;
                    throw new \Pico\PicoTestException("Token yang Anda masukkan salah", \Pico\PicoTestException::TOKEN_INVALID);
                } else if (strtotime($tokenObj->time_expire) < time()) {
                    $eligible = false;
                    throw new \Pico\PicoTestException("Token yang Anda masukkan kedaluarsa", \Pico\PicoTestException::TOKEN_EXPIRE);
                }
            } else {
                $eligible = false;
                throw new \Pico\PicoTestException("Anda wajib memasukkan token ujian", \Pico\PicoTestException::TOKEN_REQUIRED);
            }
        }
        return $eligible;
    }

    /**
     * Get test object
     * @param string $testID
     * @return \Pico\EduTest
     */
    public function getTest($testID)
    {
        $testID = addslashes($testID);
        $sql = "SELECT `edu_test`.* 
        FROM `edu_test` 
        WHERE `edu_test`.`test_id` = '$testID' 
        ";
        $obj = $this->database->executeQuery($sql)->fetchObject();
        $testObj = new \Pico\EduTest();
        if (!is_null($obj) && $obj !== false) {
            $prop = get_object_vars($obj);
            foreach ($prop as $key => $lock) {
                if (property_exists($testObj, $key)) {
                    $testObj->$key = $obj->$key;
                }
            }
        }
        return $testObj;
    }

    /**
     * Get token object
     * @param string $token
     * @param string $testId
     * @param string $studentId
     * @return \Pico\EduToken
     */
    public function getToken($token, $test, $studentId)
    {
        $testId = addslashes($test->test_id);
        $token = addslashes($token);

        $sql = "SELECT `edu_token`.* 
        FROM `edu_token`
        WHERE `edu_token`.`token` = '$token' 
        AND `edu_token`.`test_id` = '$testId' 
        AND `edu_token`.`student_id` = '$studentId' 
        ";
        
        $obj = $this->database->executeQuery($sql)->fetchObject();

        $tokenObj = new \Pico\EduToken();
        if (!is_null($obj) && $obj !== false) {
            $prop = get_object_vars($obj);
            foreach ($prop as $key => $lock) {
                if (property_exists($tokenObj, $key)) {
                    $tokenObj->$key = $obj->$key;
                }
            }
        }
        return $tokenObj;
    }

    /**
     * Get the value of message
     * @return string Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get question
     *
     * @param string $testId
     * @return array
     */
    public function getQuestion($testId, $random = false, $max = 0)
    {
        $sql = "SELECT `edu_question`.*
        FROM `edu_question` WHERE `test_id` = '$testId'
        ORDER BY `sort_order` ASC, `question_id` ASC
        ";    
        $stmt = $this->database->executeQuery($sql);
        if($stmt->rowCount() > 0)
        {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return array();
    }

    public function loadQuestion($testId, $studentId)
    {
        $sql = "SELECT `edu_test`.*  
        FROM `edu_test` 
        WHERE `edu_test`.`active` = true
        AND `edu_test`.`test_id` = '$testId' 
        ";
        $stmt = $this->database->executeQuery($sql);
        if($stmt->rowCount() > 0)
        {
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            $question_per_page = $data['question_per_page'];
            $alert_message = $data['alert_message'];
            $has_alert = $data['has_alert'];
            $alert_time = $data['alert_time'];
            $autosubmit = 0;
            if(isset($data['autosubmit']))
            {
                $autosubmit = $data['autosubmit'];
            }
            $curtime = date('Y-m-d H:is');

            $question_package = addslashes($question_package);
            $sql = "SELECT `edu_question`.* , instr('$question_package', `edu_question`.`question_id`) AS `sort_order`
            FROM `edu_question`
            where '$question_package' like concat('%[',`edu_question`.`question_id`,']%') 
            ORDER BY `sort_order` ASC
            ";
            $stmt = $this->database->executeQuery($sql);
            $number_of_question = $stmt->rowCount();
            $no_halaman_awal = 0;
            $no_halaman_akhir = 0;

            if($number_of_question)
            {
                $offset_maksimum = floor($number_of_question/$question_per_page);
                $jumlah_halaman = floor($number_of_question/$question_per_page);
                if($offset_maksimum == $number_of_question/$question_per_page)
                {
                    $offset_maksimum = ($number_of_question/$question_per_page) - 1;
                }
                $question_per_page = $question_per_page * 1;
                
                $sql = "SELECT `edu_question`.* , instr('$question_package', `edu_question`.`question_id`) AS `sort_order`
                FROM `edu_question`
                where '$question_package' like concat('%[',`edu_question`.`question_id`,']%') 
                ORDER BY `sort_order`
                ";
                $stmt1 = $this->database->executeQuery($sql);
                $question_set = array();
                $questions = array();
                if ($stmt1->rowCount() > 0) {
                    $rows1 = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
                    foreach($rows1 as $data1)
                    {
                        $soal = $data1['question_id'];
                        $question_set[] = $soal;
                        if ($data['random']) {
                            $sql2 = "SELECT `edu_option`.* , rand() AS `rand`
                            FROM `edu_option`
                            WHERE `edu_option`.`question_id` = '$soal'
                            ORDER BY `rand` ASC
                            ";
                        } else {
                            $sql2 = "SELECT `edu_option`.* , rand() AS `rand`
                            FROM `edu_option`
                            WHERE `edu_option`.`question_id` = '$soal'
                            ORDER BY `sort_order` ASC
                            ";
                        }
                        $options = array();
                        $stmt2 = $this->database->executeQuery($sql2);
                        if ($stmt2->rowCount() > 0) {
                            $rows2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
                            foreach ($rows2 as $data2) {
                                $answer = @$_SESSION['answer_tmp'][$studentId][$testId]['answer_' . $data2['question_id']];
                                $option = new \stdClass();
                                $option->option_id = $data2['option_id'];
                                $option->text = $data2['content'];
                                $options[] = $option;
                            }
                        }
                        $question = new \stdClass();
                        $question->question_id = $data1['question_id'];
                        $question->text = $data1['content'];
                        $question->numbering = $data1['numbering'];
                        $question->random = $data1['random'];
                        $question->options = $options;
                        $questions[] = $question;
                    }
                }
            }
            $storage_key = md5($studentId."-".$testId."|".implode(",",$question_set)); 
        }
    }
}
