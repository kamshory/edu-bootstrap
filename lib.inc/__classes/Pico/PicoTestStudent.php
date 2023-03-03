<?php

namespace Pico;

class PicoTestStudent
{
    public $test_id = '';
    public $school_id = '';
    public $name = '';
    public $class = '';
    public $school_program_id = '';
    public $subject = '';
    public $teacher_id = '';
    public $description = '';
    public $guidance = '';
    public $open = false;
    public $has_limits = false;
    public $trial_limits = 1;
    public $threshold = '';
    public $assessment_methods = '';
    public $number_of_question = '';
    public $number_of_option = '';
    public $question_per_page = '';
    public $random = false;
    public $duration = 0;
    public $has_alert = false;
    public $alert_time = 0;
    public $alert_message = '';
    public $standard_score = '';
    public $penalty = 0;
    public $order = 0;
    public $score_notification = '';
    public $publish_answer = false;
    public $time_answer_publication = '';
    public $test_availability = '';
    public $available_from = '';
    public $available_to = '';
    public $autosubmit = false;
    public $time_create = '';
    public $time_edit = '';
    public $member_create = '';
    public $role_create = '';
    public $member_edit = '';
    public $role_edit = '';
    public $ip_create = '';
    public $ip_edit = '';
    public $active = false;

    public function load($dcod)
    {
        $prop = get_object_vars($dcod);
        foreach ($prop as $key => $lock) {
            if (property_exists($this, $key)) {              
                $this->$key = $dcod->$key;
            }
        }
    }
}
