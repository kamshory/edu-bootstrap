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
     * @param string $test_id
     * @param \Pico\AuthStudent
     * @param string $token
     * @return bool
     */
    public function eligible($test_id, $student, $token = "")
    {
        $test = $this->getTest($test_id);
        $eligible = false;
        if ($test->open) {
            $eligible = true;
        } else {
            if (stripos($test->class, $student->class_id) !== false) {
                $eligible = true;
            } else {
                $this->message = "Ujian ini bukan untuk Anda";
            }
        }
        if ($eligible) {
            $now = time();
            if ($test->test_availability == 'L' && (strtotime($test->available_from) > $now || strtotime($test->available_to) < $now)) {
                $eligible = false;
                $this->message = "Ujian ini tersedia antara " . $test->available_from . " hingga " . $test->available_to;
            }
        }
        if($eligible && $student->use_token) {
            if(!empty($token))
            {
                
            }
            else
            {
                $eligible = false;
                $this->message = "Anda wajib memasukkan token ujian";
            }
        }
        return $eligible;
    }

    /**
     * Get test object
     * @param string $test_id
     * @return \Pico\PicoTestStudent
     */
    public function getTest($test_id)
    {
        $test_id = addslashes($test_id);
        $sql = "SELECT `edu_test`.* FROM `edu_test` WHERE `edu_test`.`test_id` = '$test_id' ";
        $obj = $this->database->executeQuery($sql)->fetchObject();

        $test = new \Pico\PicoTestStudent();

        $prop = get_object_vars($obj);
        foreach ($prop as $key => $lock) {
            if (property_exists($test, $key)) {
                $test->$key = $obj->$key;
            }
        }
        return $test;
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
