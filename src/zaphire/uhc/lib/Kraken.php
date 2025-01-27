<?php

namespace zaphire\uhc\lib;

use CURLFile;
use zaphire\uhc\ZaphireUHC;

class Kraken
{
    /** @var array|array[] */
    protected $auth = [];
    /** @var int|mixed */
    private $timeout;
    /** @var array|mixed */
    private $proxyParams;

    /**
     * Kraken constructor.
     * @param int $timeout
     * @param array $proxyParams
     */
    public function __construct($timeout = 30, $proxyParams = [])
    {
        $this->auth = [
            "auth" => [
                "api_key" => $this->getPlugin()->getDataProvider()->getKrakenKey(),
                "api_secret" => $this->getPlugin()->getDataProvider()->getKrakenSecret()
            ]
        ];
        $this->timeout = $timeout;
        $this->proxyParams = $proxyParams;
    }

    /**
     * @param array $opts
     * @return array|mixed
     */
    public function url($opts = [])
    {
        $data = json_encode(array_merge($this->auth, $opts));
        return self::request($data, 'https://api.kraken.io/v1/url', 'url');
    }

    /**
     * @param array $opts
     * @return array|mixed
     */
    public function upload($opts = [])
    {
        if (!isset($opts['file'])) {
            return [
                "success" => false,
                "error" => "File parameter was not provided"
            ];
        }
        if (!file_exists($opts['file'])) {
            return [
                "success" => false,
                "error" => 'File `' . $opts['file'] . '` does not exist'
            ];
        }

        if (class_exists('CURLFile')) {
            $file = new CURLFile($opts['file']);
        } else {
            $file = '@' . $opts['file'];
        }
        unset($opts['file']);
        $data = array_merge([
            "file" => $file,
            "data" => json_encode(array_merge($this->auth, $opts))
        ]);
        return self::request($data, 'https://api.kraken.io/v1/upload', 'upload');
    }

    /**
     * @return array|mixed
     */
    public function status(): array
    {
        $data = array('auth' => array(
            'api_key' => $this->auth['auth']['api_key'],
            'api_secret' => $this->auth['auth']['api_secret']
        ));
        return self::request(json_encode($data), 'https://api.kraken.io/user_status', 'url');
    }

    /**
     * @param $data
     * @param $url
     * @param $type
     * @return array|mixed
     */
    private function request($data, $url, $type): array
    {
        $curl = curl_init();
        if ($type === 'url') {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.85 Safari/537.36");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_FAILONERROR, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        if (isset($this->proxyParams['proxy'])) {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxyParams['proxy']);
        }
        $response = json_decode(curl_exec($curl), true);
        if ($response === null) {
            $response = array(
                "success" => false,
                "error" => 'cURL Error: ' . curl_error($curl)
            );
        }
        curl_close($curl);
        return $response;
    }

    /**
     * @return ZaphireUHC
     */
    public function getPlugin(): ZaphireUHC
    {
        return ZaphireUHC::getInstance();
    }
}