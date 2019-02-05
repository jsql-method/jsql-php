<?php

class JsqlConfig
{
    private static $apiKey;
    private static $memberKey;
    private static $apiUrl;

    /**
     * @return mixed
     */
    public static function getApiKey()
    {
        return self::$apiKey;
    }

    /**
     * @return mixed
     */
    public static function getMemberKey()
    {
        return self::$memberKey;
    }

    /**
     * @return mixed
     */
    public static function getApiUrl()
    {
        return self::$apiUrl;
    }

    public static function getJsqlConfig()
    {
        $file = '../jsql_config.ini';
        if (!$settings = parse_ini_file($file, TRUE)) throw new exception('Unable to open ' . $file . '.');

        self::$apiKey = $settings['jsql']['apiKey'];
        self::$memberKey = $settings['jsql']['memberKey'];
        self::$apiUrl = 'http://softwarecartoon.com:9291/api/request/';
    }


}