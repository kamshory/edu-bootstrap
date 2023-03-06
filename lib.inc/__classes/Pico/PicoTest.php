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
     * @param \Pico\PicoTestStudent $test
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
     * @return \Pico\PicoTestStudent
     */
    public function getTest($testID)
    {
        $testID = addslashes($testID);
        $sql = "SELECT `edu_test`.* 
        FROM `edu_test` 
        WHERE `edu_test`.`test_id` = '$testID' 
        ";
        $obj = $this->database->executeQuery($sql)->fetchObject();

        $testObj = new \Pico\PicoTestStudent();
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
     * @return \Pico\PicoTestToken
     */
    public function getToken($token, $testId, $studentId)
    {
        $token = addslashes($token);
        $sql = "SELECT `edu_token`.* 
        FROM `edu_token`
        WHERE `edu_token`.`token` = '$token' 
        AND `edu_token`.`test_id` = '$testId' 
        AND `edu_token`.`student_id` = '$studentId' 
        ";
        $obj = $this->database->executeQuery($sql)->fetchObject();

        $tokenObj = new \Pico\PicoTestToken();
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
}
