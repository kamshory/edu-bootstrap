<?php

namespace Pico;

class EduTest
{
    /**
     * Test ID
     *
     * @var string
     */
    public $test_id = '';

    /**
     * School ID
     *
     * @var string
     */
    public $school_id = '';

    /**
     * Test name
     *
     * @var string
     */
    public $name = '';

    /**
     * Class list
     *
     * @var string
     */
    public $class = '';

    /**
     * Schoop program ID
     *
     * @var string
     */
    public $school_program_id = '';

    /**
     * Test subject
     *
     * @var string
     */
    public $subject = '';

    /**
     * Techer ID
     *
     * @var string
     */
    public $teacher_id = '';

    /**
     * Test description
     *
     * @var string
     */
    public $description = '';

    /**
     * Test guidance
     *
     * @var string
     */
    public $guidance = '';

    /**
     * Is test open
     *
     * @var boolean
     */
    public $open = false;

    /**
     * Does test has limit
     *
     * @var boolean
     */
    public $has_limits = false;

    /**
     * Trial limit
     *
     * @var integer
     */
    public $trial_limits = 1;

    /**
     * Threshold
     *
     * @var string
     */
    public $threshold = '';

    /**
     * Assesment method
     *
     * @var string
     */
    public $assessment_methods = '';

    /**
     * Number of question
     *
     * @var integer
     */
    public $number_of_question = 0;

    /**
     * Number of option
     *
     * @var integer
     */
    public $number_of_option = 0;

    /**
     * Question per page
     *
     * @var integer
     */
    public $question_per_page = 0;

    /**
     * Is question displayed random
     *
     * @var boolean
     */
    public $random = false;

    /**
     * Is option displayed random
     *
     * @var boolean
     */
    public $random_option = false;

    /**
     * Test duration
     *
     * @var integer
     */
    public $duration = 0;

    /**
     * Has alert message
     *
     * @var boolean
     */
    public $has_alert = false;

    /**
     * When alert message displayed before test end
     *
     * @var integer
     */
    public $alert_time = 0;

    /**
     * Alert message
     *
     * @var string
     */
    public $alert_message = '';

    /**
     * Standard score
     *
     * @var float
     */
    public $standard_score = 0.0;

    /**
     * Is question displayed random and grouped by basic competence
     *
     * @var boolean
     */
    public $random_distribution = false;

    /**
     * Basic competence distribution
     *
     * @var string
     */
    public $competence_distribution = '';

    /**
     * Penalty
     *
     * @var float
     */
    public $penalty = 0.0;

    /**
     * Sort order
     *
     * @var integer
     */
    public $sort_order = 0;

    /**
     * Score notification
     *
     * @var string
     */
    public $score_notification = '';

    /**
     * Public answer
     *
     * @var boolean
     */
    public $publish_answer = false;

    /**
     * Time answer publication
     *
     * @var string
     */
    public $time_answer_publication = '';

    /**
     * Test availability
     *
     * @var string
     */
    public $test_availability = '';

    /**
     * Available from
     *
     * @var string
     */
    public $available_from = '';

    /**
     * Available to
     *
     * @var string
     */
    public $available_to = '';

    /**
     * Autosubmit answer
     *
     * @var boolean
     */
    public $autosubmit = false;

    /**
     * Time create
     *
     * @var string
     */
    public $time_create = '';

    /**
     * Time edit
     *
     * @var string
     */
    public $time_edit = '';

    /**
     * Member create
     *
     * @var string
     */
    public $member_create = '';

    /**
     * Role create
     *
     * @var string
     */
    public $role_create = '';

    /**
     * Member edit
     *
     * @var string
     */
    public $member_edit = '';

    /**
     * Role edit
     *
     * @var string
     */
    public $role_edit = '';

    /**
     * IP create
     *
     * @var string
     */
    public $ip_create = '';

    /**
     * IP edit
     *
     * @var string
     */
    public $ip_edit = '';

    /**
     * Flag test active or not
     *
     * @var boolean
     */
    public $active = false;
}
