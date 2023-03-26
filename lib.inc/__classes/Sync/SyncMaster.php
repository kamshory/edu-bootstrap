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
     * @var string
     */
    protected $application = \Pico\PicoConst::PICO_EDU;

    protected $forbiddenExtension = array(
        'php',
        'sh',
        'shell',
        'exe',
        'htaccess',
        'htpasswd',
        'ini',
        'inf',
        'bat',
        'rb',
        'phy'
    );


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

    /**
     * Get file extension
     *
     * @param string $path
     * @return string
     */
    protected function getFileExtension($path)
    {
        $ext = "";
        if (stripos($path, ".")) {
            $arr = explode(".", $path);
            $ext = end($arr);
        } else {
            $ext = $path;
        }
        return $ext;
    }



    /**
     * Set database
     *
     * @param  \Pico\PicoDatabase  $database  Database
     *
     * @return  self
     */
    public function withDatabase(\Pico\PicoDatabase $database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Set application root
     *
     * @param  string  $applicationRoot  Application root
     *
     * @return  self
     */
    public function withApplicationRoot(string $applicationRoot)
    {
        $this->applicationRoot = $applicationRoot;

        return $this;
    }

    /**
     * Set upload base directory
     *
     * @param  string  $uploadBaseDir  Upload base directory
     *
     * @return  self
     */
    public function withUploadBaseDir(string $uploadBaseDir)
    {
        $this->uploadBaseDir = $uploadBaseDir;

        return $this;
    }

    /**
     * Set download base directory
     *
     * @param  string  $downloadBaseDir  Download base directory
     *
     * @return  self
     */
    public function withDownloadBaseDir(string $downloadBaseDir)
    {
        $this->downloadBaseDir = $downloadBaseDir;

        return $this;
    }

    /**
     * Set pooling file base directory
     *
     * @param  string  $poolBaseDir  Pooling file base directory
     *
     * @return  self
     */
    public function withPoolBaseDir(string $poolBaseDir)
    {
        $this->poolBaseDir = $poolBaseDir;

        return $this;
    }

    /**
     * Set pooling file name
     *
     * @param  string  $poolFileName  Pooling file name
     *
     * @return  self
     */
    public function withPoolFileName(string $poolFileName)
    {
        $this->poolFileName = $poolFileName;

        return $this;
    }

    /**
     * Set pooling file prefix
     *
     * @param  string  $poolRollingPrefix  Pooling file prefix
     *
     * @return  self
     */
    public function withPoolRollingPrefix(string $poolRollingPrefix)
    {
        $this->poolRollingPrefix = $poolRollingPrefix;

        return $this;
    }

    /**
     * Set pooling file extension
     *
     * @param  string  $poolFileExtension  Pooling file extension
     *
     * @return  self
     */
    public function withPoolFileExtension(string $poolFileExtension)
    {
        $this->poolFileExtension = $poolFileExtension;

        return $this;
    }

    /**
     * Set application code
     *
     * @param  string  $application  Application code
     *
     * @return  self
     */
    public function withApplication(string $application)
    {
        return $this->setApplication($application);
    }
}
