<?php

namespace Zhengyaocheng\SimpleCurl;

class SimpleCurl
{
    protected static $type          = 'GET';
    protected static $url           = '';
    protected static $ssl_verify    = false;
    protected static $return_header = false;
    protected static $headers       = [];
    protected static $data          = '';
    protected static $timeout       = 10;

    protected static function curl_options(): array
    {
        $options = [
            CURLOPT_URL            => self::$url,
            CURLOPT_HEADER         => self::$return_header,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::$timeout,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ];

        //ssl 证书验证
        if (self::$ssl_verify){
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            $options[CURLOPT_SSL_VERIFYHOST] = 2;
            //$options[CURLOPT_CAINFO]         = '';
            //$options[CURLOPT_CAPATH]         = '';
        }

        //has headers
        if (self::$headers){
            $options[CURLOPT_HTTPHEADER]  = self::$headers;
        }

        //get
        if (self::$type === 'GET'){
            $options[CURLOPT_CUSTOMREQUEST]  = 'GET';
            $options[CURLOPT_POSTFIELDS]     = self::$data;
        }

        //post
        if (self::$type === 'POST'){
            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = self::$data;
        }

        return $options;
    }

    /**
     * 发送请求
     * @return array
     */
    protected static function send(): array
    {
        $ch = \curl_init();
        \curl_setopt_array($ch, self::curl_options());
        $response = \curl_exec($ch);

        $request_header  = ''; //请求头
        $response_header = ''; //响应头
        $body            = $response; //响应主体
        if (self::$return_header){
            $curl_info       = \curl_getinfo($ch);
            $request_header  = \array_key_exists("request_header", $curl_info)? $curl_info["request_header"] : '';
            $response_header = \substr($response, 0, $curl_info['header_size']);
            $body            = \substr($response, $curl_info['header_size']);
        }

        //$res_error       = curl_error($ch);
        \curl_close($ch);

        return ['request_header'=>$request_header, 'response_header'=>$response_header, 'body'=>$body];
    }

    /**
     * set url
     * @param $url
     * @return SimpleCurl
     */
    public static function to($url): SimpleCurl{
        self::$url = $url;
        return new self;
    }

    /**
     * set headers
     * @param array $headers
     * @return $this
     */
    public function headers(array $headers): SimpleCurl{
        self::$headers = $headers;
        return $this;
    }

    /**
     * @param $data
     * @param bool $as_json
     * @return $this
     */
    public function params($data, bool $as_json=true): SimpleCurl{
        if (\is_array($data) and  $as_json) {
            $data = \json_encode($data);
            self::$headers[] = 'Content-Type: application/json';
        }
        self::$data = $data;
        return $this;
    }

    /**
     * set timeout
     * @param int $second
     * @return $this
     */
    public function timeout(int $second): SimpleCurl{
        self::$timeout = $second;
        return $this;
    }

    /**
     * get request
     * @param bool $return_header
     * @return array|mixed
     */
    public function get(bool $return_header=false){
        self::$return_header = $return_header;

        if ($return_header){
            return self::send();
        }else{
            return self::send()['body'];
        }
    }

    /**
     * post request
     * @param bool $return_header
     * @return array|mixed
     */
    public function post(bool $return_header=false){
        self::$type          = 'POST';
        self::$return_header = $return_header;

        if ($return_header){
            return self::send();
        }else{
            return self::send()['body'];
        }
    }
}