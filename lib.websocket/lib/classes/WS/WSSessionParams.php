<?php

namespace WS;

class WSSessionParams
{
    /**
     * Session cookie name
     */
    private $sessionCookieName = 'PHPSESSID';

    /**
     * Session save path
     */
    private $sessionSavePath = "/";

    /**
     * Session file prefix
     */
    private $sessionFilePrefix = 'sess_';

    /**
     * Constructor of \WS\WSSessionParams
     *
     * @param string $sessionCookieName Session cookie name
     * @param string $sessionSavePath Session save path
     * @param string $sessionFilePrefix Session file prefix
     */
    public function __construct($sessionCookieName = null, $sessionSavePath = null, $sessionFilePrefix = null)
    {
        if ($sessionCookieName != null) {
            $this->sessionCookieName = $sessionCookieName;
        }
        if ($sessionSavePath != null) {
            $this->sessionSavePath = $sessionSavePath;
        }
        if ($sessionFilePrefix != null) {
            $this->sessionFilePrefix = $sessionFilePrefix;
        }
    }

    /**
     * Get session cookie name
     */
    public function getSessionCookieName()
    {
        return $this->sessionCookieName;
    }

    /**
     * Get session save path
     */
    public function getSessionSavePath()
    {
        return $this->sessionSavePath;
    }

    /**
     * Set session save path
     */
    public function setSessionSavePath($sessionSavePath)
    {
        $this->sessionSavePath = $sessionSavePath;
    }

    /**
     * get session file prefix
     */
    public function getSessionFilePrefix()
    {
        return $this->sessionFilePrefix;
    }
}
