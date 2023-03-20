<?php

namespace Sync;

class SyncTime extends \Sync\SyncMaster
{
    public function __construct($application)
    {
        $this->application = $application;
    }

    /**
     * Upload sync file to sync hub
     * @param string $fileSyncUrl Synch hub URL
     * @param string $username Sync username
     * @param string $password Sync password
     * @param \Pico\PicoDatabase $database
     * @return array
     */
    public function syncTime($fileSyncUrl, $username, $password, $database) //NOSONAR
    {
        $httpQuery = array(
            'sync_type' => 'time'
        );
        $fileSyncUrl = $this->buildURL($fileSyncUrl, $httpQuery);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_URL, $fileSyncUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $post = array(
            'timezone' => $database->getDatabaseCredentials()->getTimeZone(),
            'time' => time()
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $server_output = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 
        curl_close($ch);

        if ($httpcode == 200) {
            try {
                $response = json_decode($server_output, true);
                if ($response === null && json_last_error() !== JSON_ERROR_NONE) {
                    $rc = \Sync\SyncResponseCode::INVALID_FORMAT;
                    $rt = \Sync\SyncResponseCode::getResponseText($rc);
                    return array(
                        'response_code' => $rc,
                        'response_text' => $rt
                    );
                }
            } catch (\Exception $e) {
                $rc = \Sync\SyncResponseCode::INVALID_FORMAT;
                $rt = \Sync\SyncResponseCode::getResponseText($rc);
                return array(
                    'response_code' => $rc,
                    'response_text' => $rt
                );
            }
            return $response;
        } else {
            $rc = \Sync\SyncResponseCode::FAILED;
            $rt = \Sync\SyncResponseCode::getResponseText($rc);
            return array(
                'response_code' => $rc,
                'response_text' => $rt
            );
        }
    }
}
