<?php

namespace Sync;

class SyncMaster
{
    /**
     * Database
     *
     * @var \Pico\PicoDatabase
     */
    protected $database;
    /**
     * Application root
     *
     * @var string
     */
    protected $applicationRoot = '';
    /**
     * Upload base directory
     *
     * @var string
     */
    protected $uploadBaseDir = '';
    /**
     * Download base directory
     *
     * @var string
     */
    protected $downloadBaseDir = '';
    /**
     * Pooling file base directory
     *
     * @var string
     */
    protected $poolBaseDir = '';
    /**
     * Pooling file name
     *
     * @var string
     */
    protected $poolFileName = '';
    /**
     * Pooling file prefix
     *
     * @var string
     */
    protected $poolRollingPrefix = '';
    /**
     * Pooling file extension
     *
     * @var string
     */
    protected $poolFileExtension = '';
    
    /**
     * Application code
     *
     * @var [type]
     */
    protected $application = \Pico\PicoConst::PICO_EDU;



    /**
     * Build URL
     *
     * @param string $url
     * @param array $httpQuery
     * @param boolean $keepOriginal
     * @return string
     */
    public function buildURL($url, $httpQuery, $keepOriginal = true)
    {
        $original = array();
        if ($keepOriginal) {
            $parsed = parse_url($url);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $original);
            }
        }
        $combined = array_merge($original, $httpQuery);

        if (stripos($url, "?") !== false) {
            $arr = explode("?", $url);
            $url = $arr[0];
        }
        $url = $url . "?" . http_build_query($combined);
        return $url;
    }

    /**
     * Get the value of application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the value of application
     *
     * @return self
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }
}
