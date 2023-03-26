<?php

namespace Pico;

class PicoSortQuestion
{

    /**
     * Unsorted question list
     *
     * @var array
     */
    private $unsorted = array();

    /**
     * Sorted question list
     *
     * @var array
     */
    private $unsortedOri = array();

    /**
     * Unsorted data
     *
     * @var array
     */
    private $unsortedData = array();

    /**
     * Sorted question list
     *
     * @var array
     */
    private $sorted = array();

    /**
     * Final question list
     *
     * @var array
     */
    private $final = array();

    /**
     * Total question
     *
     * @var integer
     */
    private $totalQuestion = 0;

    /**
     * Number of question 
     *
     * @var integer
     */
    private $numberOfQuestion = 0;

    /**
     * Test data
     *
     * @var array
     */
    private $testData = array();

    /**
     * Maximum number of group member
     *
     * @var integer
     */
    private $maxGroupMember = 0;

    public function __construct($testData)
    {
        $this->testData = $testData;
    }

    /**
     * Merge question
     *
     * @param array $data
     * @return array
     */
    public function mergeBasicCompetence($data)
    {

        $merged = array();
        foreach ($data as $val) {
            $bc = $val['basic_competence'];
            $merged[$bc][] = $val;
            if (!isset($this->unsortedData[$bc])) {
                $this->unsortedData[$bc] = array();
            }
            $this->unsortedData[$bc][] = $val;
        }
        return $merged;
    }

    /**
     * Process question to be displayed
     *
     * @return void
     */
    public function process()
    {
        $this->unsortedOri = array();
        $this->unsorted = array();
        $this->sorted = array();
        $this->final = array();

        $this->numberOfQuestion = $this->testData['number_of_question'];

        if ($this->testData['random_distribution']) {
            $this->randomDistribution();

            if (array_sum($this->unsorted) < $this->numberOfQuestion && $this->totalQuestion > $this->numberOfQuestion) {
                // Jumlah soal random kurang dari yang akan ditampilkan 
                $this->add();
            }
            if (array_sum($this->unsorted) > $this->numberOfQuestion) {
                // Jumlah soal random lebih dari yang akan ditampilkan 
                $this->subtract();
            }
            $this->sort();
            $this->final = $this->sorted;
        } else {
            $this->unsortedOri[''] = $this->numberOfQuestion;
            $this->unsorted = $this->unsortedOri;
            $this->sorted = $this->unsorted;
            $this->final = $this->sorted;
        }
    }

    public function randomDistribution()
    {
        $data = $this->testData['data'];
        $merged = $this->mergeBasicCompetence($data);
        foreach ($merged as $key => $val) {
            $this->unsorted[$key] = count($val);
        }
        $this->final = $this->unsorted;

        $this->unsortedOri = $this->unsorted;
        $num_group = count($merged);
        if ($num_group == 0) {
            $num_group = 1;
        }
        $this->totalQuestion = count($data);

        $this->maxGroupMember = ceil($this->numberOfQuestion / $num_group);

        foreach ($this->unsorted as $key => $val) {
            if ($val > $this->maxGroupMember) {
                $this->unsorted[$key] = $this->maxGroupMember;
            }
        }
    }

    public function add()
    {
        do {
            foreach ($this->unsorted as $key => $val) {
                if (array_sum($this->unsorted) < $this->numberOfQuestion && $val < $this->unsortedOri[$key]) {
                    $this->unsorted[$key]++;
                }
            }
        } while (array_sum($this->unsorted) < $this->numberOfQuestion && array_sum($this->unsorted) < $this->totalQuestion);
    }

    public function subtract()
    {
        $countRev = array_reverse($this->unsorted);
        foreach ($countRev as $key => $val) {
            if (array_sum($countRev) > $this->numberOfQuestion && $val < $this->unsortedOri[$key]) {
                $this->unsorted[$key]--;
                $countRev[$key]--;
            }
        }

        arsort($countRev);

        foreach ($countRev as $key => $val) {
            if (array_sum($countRev) > $this->numberOfQuestion) {
                $this->unsorted[$key]--;
                $countRev[$key]--;
            }
        }
    }

    public function toNumber($str)
    {
        $str = str_replace(".", "", $str);
        return ((int) $str);
    }

    public function sort()
    {
        $keys = array_keys($this->unsorted);
        $keys2 = array();
        foreach ($keys as $key => $val) {
            if (stripos($val, ".") !== false) {
                $arr = explode(".", $val, 2);
                $keys2[$val] = ($this->toNumber($arr[0]) * 1000) + $this->toNumber($arr[1]);
            } else if (!empty($val)) {
                $keys2[$val] = ($this->toNumber($val) * 1000);
            } else {
                $keys2[$val] = 0;
            }
        }
        asort($keys2);
        $keys = array_keys($keys2);
        foreach ($keys as $key) {
            $this->sorted[$key] = $this->unsorted[$key];
        }
    }

    /**
     * Get random question
     *
     * @return array
     */
    public function getRandom()
    {
        $result = array();
        if ($this->testData['random_distribution']) {
            foreach ($this->final as $key => $val) {
                $rand = $this->unsortedData[$key];
                shuffle($rand);
                for ($i = 0; $i < $val; $i++) {
                    $result[] = $rand[$i];
                }
            }
        } else if ($this->testData['random']) {
            $rand = $this->testData['data'];
            shuffle($rand);
            for ($i = 0; $i < $this->numberOfQuestion; $i++) {
                $result[] = $rand[$i];
            }
        } else {
            $rand = $this->testData['data'];
            for ($i = 0; $i < $this->numberOfQuestion; $i++) {
                $result[] = $rand[$i];
            }
        }
        return $result;
    }


    /**
     * Get the value of unsorted
     */
    public function getUnsorted()
    {
        return $this->unsorted;
    }

    /**
     * Get the value of unsortedOri
     */
    public function getUnsortedOri()
    {
        return $this->unsortedOri;
    }

    /**
     * Get the value of sorted
     */
    public function getSorted()
    {
        return $this->sorted;
    }

    /**
     * Get the value of totalQuestion
     */
    public function getTotalQuestion()
    {
        return $this->totalQuestion;
    }

    /**
     * Get the value of numberOfQuestion
     */
    public function getNumberOfQuestion()
    {
        return $this->numberOfQuestion;
    }

    /**
     * Get the value of final
     */
    public function getFinal()
    {
        return $this->final;
    }
}
