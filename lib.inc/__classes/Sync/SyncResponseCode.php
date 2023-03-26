<?php

namespace Sync;

class SyncResponseCode
{
    const SUCCESS = '00';
    const INVALID_FORMAT = '02';
    const FAILED = '03';

    /**
     * Get response text
     *
     * @param string $code
     * @return string
     */
    public static function getResponseText($code)
    {
        $rt = '';
        if ($code == self::SUCCESS) {
            $rt = 'Sukses';
        } else if ($code == self::INVALID_FORMAT) {
            $rt = 'Respon tidak sesuai spesifikasi';
        } else if ($code == self::FAILED) {
            $rt = 'Gagal';
        }
        return $rt;
    }
}
