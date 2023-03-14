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
     * @param \Pico\EduTest $test Test
     * @param string $token Token
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
                $tokenObj = $this->getToken($token, $test, $student->student_id);
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
            foreach ($prop as $key => $value) {
                if (property_exists($testObj, $key)) {
                    $type = \Pico\PicoType::getType($testObj->$key);
                    $testObj->$key = \Pico\PicoType::valueOf($obj->$key, $type);
                }
            }
        }
        return $testObj;
    }

    /**
     * Get token object
     * @param string $token
     * @param \Pico\EduTest|null $test
     * @param string $studentId
     * @return \Pico\EduToken
     */
    public function getToken($token, $test, $studentId)
    {
        $filter = "";
        if ($test != null) {
            $testId = addslashes($test->test_id);
            $filter .= " AND `edu_token`.`test_id` = '$testId' 
            ";
        }
        $token = addslashes($token);

        $sql = "SELECT `edu_token`.* 
        FROM `edu_token`
        WHERE `edu_token`.`token` = '$token' 
        AND `edu_token`.`student_id` = '$studentId' 
        $filter
        ";
        $obj = $this->database->executeQuery($sql)->fetchObject();
        $tokenObj = new \Pico\EduToken();
        if (!is_null($obj) && $obj !== false) {
            $prop = get_object_vars($obj);
            foreach ($prop as $key => $value) {
                if (property_exists($tokenObj, $key)) {
                    $type = \Pico\PicoType::getType($tokenObj->$key);
                    $tokenObj->$key = \Pico\PicoType::valueOf($obj->$key, $type);
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
     * @param string $list
     * @param \Pico\EduTest $test
     * @return array
     */
    public function getQuestion($list, $test)
    {
        $listQuestion = explode(",", $list);
        $result = array();
        foreach ($listQuestion as $key => $val) {
            $questionId = addslashes($val);
            $sql = "SELECT `question_id`, `basic_competence`, `multiple_choice`, `random`, `numbering`, `content`
            FROM `edu_question` WHERE `question_id` = '$questionId'       
            ";
            $stmt = $this->database->executeQuery($sql);
            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(\PDO::FETCH_ASSOC);
                $data['number'] = (int) $key + 1;
                $questionId = addslashes($val);
                $sql = "SELECT `option_id`, `sort_order`, `content`
                FROM `edu_option` WHERE `question_id` = '$questionId'       
                ";
                $stmtOption = $this->database->executeQuery($sql);
                if ($stmtOption->rowCount() > 0) {
                    $dataOption = $stmtOption->fetchAll(\PDO::FETCH_ASSOC);
                    if ($data['random'] && $test->random_option) {
                        shuffle($dataOption);
                    }
                    $dataOption[] = array(
                        'option_id' => '',
                        'sort_order' => count($dataOption),
                        'content' => 'Tidak menjawab'
                    );
                    $data['option'] = $dataOption;
                }
                $result[] = $data;
            }
        }
        return $result;
    }

    /**
     * Undocumented function
     *
     * @param \Pico\EduTest $eduTest Test
     * @param array $question Question
     * @return array
     */
    public function getTestData($eduTest, $question)
    {
        return array('test' => $eduTest, 'data' => $question);
    }

    /**
     * Get question list
     * @param \Pico\AuthStudent $studentLoggedIn
     * @param \Pico\EduTest $eduTest
     * @param array $eduTestAnswer
     * @return string
     */
    public function getQuestionList($studentLoggedIn, $eduTest, $eduTestAnswer)
    {
        $saved = $this->getSavedQuestionList($studentLoggedIn, $eduTest, $eduTestAnswer);
        if ($saved == null) {
            $saved = $this->generateQuestionList($eduTest);
        }
        return $saved;
    }

    /**
     * Get test answer of student
     *
     * @param \Pico\AuthStudent $studentLoggedIn
     * @param \Pico\EduTest $eduTest
     * @return array|null
     */
    public function getTestAnswer($studentLoggedIn, $eduTest)
    {
        $studentId = addslashes($studentLoggedIn->student_id);
        $testId = addslashes($eduTest->test_id);
        $sql = "SELECT `edu_answer`.*
        FROM `edu_answer` 
        WHERE `edu_answer`.`student_id` = '$studentId'
        AND `edu_answer`.`test_id` = '$testId' 
        AND `edu_answer`.`finish` = false
        ";
        $stmt = $this->database->executeQuery($sql);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function createTestAnswer($studentLoggedIn, $eduTest, $list)
    {
        $answer_id = $this->database->generateNewId();
        $school_id = $studentLoggedIn->school_id;
        $student_id = $studentLoggedIn->student_id;
        $start = "'" . date('Y-m-d H:i:s') . "'";
        $end = "null";

        $test_id = $eduTest->test_id;
        $question_list = addslashes($list);

        $sql = "INSERT INTO `edu_answer` 
        (`answer_id`, `school_id`, `test_id`, `student_id`, `start`, `end`, `question_list`, `answer`, `answer_true`, `answer_false`, `initial_score`, `penalty`, `final_score`, `competence_score`, `percent`, `finish`, `active`) VALUES
        ('$answer_id', '$school_id', '$test_id', '$student_id', $start, $end, '$question_list', NULL, 0, 0, 0, 0, 0, NULL, 0, false, true);
        ";
        $this->database->executeInsert($sql, true);
    }

    /**
     * Get question list
     * @param \Pico\EduTest $eduTest
     * @return string
     */
    public function generateQuestionList($eduTest)
    {
        $testId = addslashes($eduTest->test_id);
        $sql = "SELECT `edu_question`.`question_id`, `edu_question`.`basic_competence` 
        FROM `edu_question` 
        WHERE `edu_question`.`test_id` = '$testId' 
        AND `edu_question`.`active` = true
        ";
        $stmt = $this->database->executeQuery($sql);
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($data as $key => $val) {
                $data[$key]['number'] = (int)$key + 1;
            }
        } else {
            $data = array();
        }
        $testData['number_of_question'] = $eduTest->number_of_question;
        $testData['random_distribution'] = $eduTest->random_distribution;
        $testData['random'] = $eduTest->random;
        $testData['data'] = $data;

        $obj = new \Pico\PicoSortQuestion($testData);
        $obj->process();
        $random = $obj->getRandom();
        $result = array();
        foreach ($random as $key => $val) {
            $result[] = $val['question_id'];
        }
        return implode(",", $result);
    }

    /**
     * Get question list
     * @param \Pico\AuthStudent $studentLoggedIn
     * @param \Pico\EduTest $eduTest
     * @param array $eduTestAnswer
     * @return string|null
     */
    public function getSavedQuestionList($studentLoggedIn, $eduTest, $eduTestAnswer)
    {
        if ($eduTestAnswer != null && $studentLoggedIn->student_id == $eduTestAnswer['student_id'] && $eduTest->test_id == $eduTestAnswer['test_id']) {
            return $eduTestAnswer['question_list'];
        }
        return null;
    }
}
