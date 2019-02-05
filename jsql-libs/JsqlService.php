<?php

include_once '../jsql-libs/JsqlConfig.php';

class JsqlService
{
    public static $arrayOfCachedOptions = [];

    public static function initJsqlConfig(): void
    {
        JsqlConfig::getJsqlConfig();
    }

    public static function initJsqlCache(): void
    {
        if (JsqlService::isOptionsCached(100)) {
            self::$arrayOfCachedOptions = JsqlService::getCacheOptionsArrayFromFile();
        } else {
            JsqlService::setCacheOptionsToFile();
            self::$arrayOfCachedOptions = JsqlService::getCacheOptionsArrayFromFile();
        }
    }

    public static function callJsqlApi($data, $method, $endpoint)
    {
        $apiKey = 'apiKey:' . JsqlConfig::getApiKey();
        $memberKey = 'memberKey:' . JsqlConfig::getMemberKey();
        $apiUrl = JsqlConfig::getApiUrl();
        $headers = [
            'Content-Type: application/json',
            $apiKey,
            $memberKey
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        curl_close ($ch);

        return json_decode((string)$server_output, true);
    }

    public static function isOptionsCached(int $cacheTimeInMinutes): bool
    {
        if (!file_exists('../cache')) {
            mkdir("../cache");
        }

        $cachedFile = '../cache/cached-' . self::getUniqueCacheFilename() . '.txt'; //location of cache file

        if(file_exists($cachedFile)){ //check if cache file exists and hasn't expired yet
            $current_time = time();
            $cache_last_modified = filemtime($cachedFile); //time when the cache file was last modified

            if ($current_time < strtotime("+{$cacheTimeInMinutes} minutes", $cache_last_modified)) {
                return  true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function setCacheOptionsToFile():void
    {
        $resOptions = serialize(JsqlService::callJsqlApi(null, 'GET', 'options/all'));
        $fp = fopen('../cache/cached-' . self::getUniqueCacheFilename() . '.txt', 'w'); //open cache file
        fwrite($fp, $resOptions); //create new cache file
        fclose($fp); //close cache file
    }

    public static function getCacheOptionsArrayFromFile():array
    {
        $arrayOfCachedOptions = [];

        $fileContent = file_get_contents('../cache/cached-' . self::getUniqueCacheFilename() . '.txt');
        $databaseDialectValue = self::getSpecificOption('databaseDialect', $fileContent);
        $arrayOfCachedOptions['databaseDialect'] = $databaseDialectValue;
        $applicationLanguageValue = self::getSpecificOption('applicationLanguage', $fileContent);
        $arrayOfCachedOptions['applicationLanguage'] = $applicationLanguageValue;
        $prodValue = self::getSpecificOption('saltBefore', $fileContent);
        $arrayOfCachedOptions['saltBefore'] = $prodValue;

        return $arrayOfCachedOptions;
    }

    public static function getUniqueCacheFilename():string
    {
        return str_replace("/","",JsqlConfig::getApiKey());

    }

    public static function getSpecificOption(string $specificOptionName, string $fileContent):?string
    {
        if (self::is_serialized($fileContent)) {
            $fileContentArray = unserialize($fileContent);
            $specificOptionValue = $fileContentArray['data'][$specificOptionName];

            return $specificOptionValue;
        } else {
            $pieces = explode(",", $fileContent);

            for ($i=0, $j=count($pieces); $i <= $j; $i++) {
                foreach($pieces as $k => $v){
                    //If stristr, add the index to our
                    //$indexes array.
                    if(stristr($v, $specificOptionName)){
                        $piece = stristr($v, $specificOptionName);
                        $pieces = explode(':', $piece);
                        return $specificOptionValue = trim($pieces[1], "\"");
                    }
                }
            }

            return null;
        }
    }

    public static function is_serialized($data):bool
    {
        // if it isn't a string, it isn't serialized
        if ( !is_string( $data ) )
            return false;
        $data = trim( $data );
        if ( 'N;' == $data )
            return true;
        if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
            return false;
        switch ( $badions[1] ) {
            case 'a' :
            case 'O' :
            case 's' :
                if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                    return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                    return true;
                break;
        }
        return false;
    }

    /**
     * Transform $data into CURLOPT_POSTFIELDS data e.g. '["$data"]'
     *
     * @param $data
     * @return string
     */
    public static function transformDataToString($data):string
    {
        if (is_array($data)) {
            $str = implode('","', $data);
            $str = '["' . $str . '"]';
        } else {
            $str = '["' . $data . '"]';
        }

        return $str;
    }

    public static function glueTokensToString(array $arr):string
    {
        $tokensArray = [];

        foreach ($arr as $item) {
            $tokensArray[] = $item["query"];
        }

        $str = implode(" ", $tokensArray);

        return $str;
    }

    public static function is_multiArray(array $arr): bool
    {
        $rv = array_filter($arr,'is_array');
        if(count($rv)>1) {
            return true;
        }

        return false;
    }

    public static function safe_json_encode($value, $options = 0, $depth = 512) {
        $encoded = json_encode($value, $options, $depth);
        if ($encoded === false && $value && json_last_error() == JSON_ERROR_UTF8) {
            $encoded = json_encode(self::utf8ize($value), $options, $depth);
        }
        return $encoded;
    }

    private static function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                unset($d[$k]);
                $d[self::utf8ize($k)] = self::utf8ize($v);
            }
        } else if (is_object($d)) {
            $objVars = get_object_vars($d);
            foreach($objVars as $key => $value) {
                $d->$key = self::utf8ize($value);
            }
        } else if (is_string ($d)) {
            return iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($d));
        }
        return $d;
    }

    public static function isJsonCorrect(string $jsonData): array
    {
        $res = [];

        json_decode($jsonData, true);

        switch(json_last_error())
        {
            case JSON_ERROR_DEPTH:
                $res["isJsonCorrect"] = false;
                $res["msgError"] = 'The maximum level of data nesting has been exceeded!';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $res["isJsonCorrect"] = false;
                $res["msgError"] = 'Underflow or the modes mismatch!';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $res["isJsonCorrect"] = false;
                $res["msgError"] = 'Unexpected control character found!';
                break;
            case JSON_ERROR_SYNTAX:
                $res["isJsonCorrect"] = false;
                $res["msgError"] = 'Syntax error. Badly constructed JSON!';
                break;
            case JSON_ERROR_UTF8:
                $res["isJsonCorrect"] = false;
                $res["msgError"] = 'Malformed UTF-8 characters found!';
                break;
            case JSON_ERROR_NONE:
                $res["isJsonCorrect"] = true;
                $res["msgError"] = "No errors. Json is correct.";
                $res["JsonDecodedAssocArray"] = json_decode($jsonData, true);
                break;
            default:
                $res["isJsonCorrect"] = false;
                $res["msgError"] = "Json incorrect. Undeclared error!";
        }

        return $res;
    }

//    public static function encodeFile(String $inputString): array
//    {
//        $role = 'lkwdlkcwlkflkwflkwlwkdmvcdvc';
//        $session = "";
//        $key = [];
//        $inputString = mb_convert_encoding($inputString, "UTF-8");
//        $r = mb_strlen($role) - 1;
//
//        for ($i = 0; $i < mb_strlen($inputString); $i++) {
//            if ($i % 3 === 0 && $r >= 0) {
//                $session .= mb_substr($role, $r, 1);
//                (array_push($key, "{\"r\":\"$i\"}"));
//
//                $r--;
//                if ($r<0) {
//                    $r = mb_strlen($role) - 1;
//                }
//
//                $n = (mb_substr($inputString, $i, 1));
//                array_push($key, "{\"s\":\"$n\"}");
//            } else {
//                $session .= (mb_substr($inputString, $i, 1));
//            }
//        }
//
//        return [$session, json_encode($key)];
//    }

//    public static function decodeFile(array $hashData):string
//    {
//        ($decodedData = $hashData[0]);
//        ($token = $decodedData);
//        ($key = json_decode($hashData[1]));
//
//
//        for ($i = 0; $i < count($key); $i++) {
//            if (property_exists(json_decode($key[$i]), 'r')) {
//                $splitted = self::mbStringToArray($token);
//                $splitIndex = (json_decode($key[$i])->r);
//            }
//            if (property_exists(json_decode($key[$i]), 's')) {
//                $splitted[(int)$splitIndex] = (json_decode($key[$i])->s);
//                $token = implode("", $splitted);
//            }
//        }
//
//      return $token;
//    }

    public static function mbStringToArray( $string)
    {
        $stop   = mb_strlen( $string);
        $result = array();

        for( $idx = 0; $idx < $stop; $idx++)
        {
            $result[] = mb_substr( $string, $idx, 1);
        }

        return $result;
    }
}