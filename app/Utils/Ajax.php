<?php

namespace App\Utils;

use Exception;
use phpseclib3\Crypt\AES;

class Ajax {
    protected $aes;

    public function __construct($key, $iv) {
        if (!$key || !$iv) {
            throw new Exception("No key nor iv provided");
        }

        $this->aes = new AES('cbc');
        $this->aes->setKey($key);
        $this->aes->setIV($iv);
    }
    public function generateAjaxParams(string $token, string $video_id): string {
        $encrypted_key = base64_encode($this->aes->encrypt($video_id));

        return "id=$encrypted_key&alias=$video_id&$token";
    }

    /**
     * Summary of decryptToken
     * @param string $token
     * @return string
     */
    public function decryptToken(string $token): string {
        $decoded_data = base64_decode($token);
        $token        = rtrim($this->aes->decrypt($decoded_data), "\0");
        return $token;
    }

    public function decryptAjaxData(string $data) {
        $this->aes->disablePadding();
        $decrypted = rtrim($this->aes->decrypt(base64_decode($data)), "\0..\32");
        return json_decode($decrypted, true);
    }
}
