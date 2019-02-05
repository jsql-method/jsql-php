<?php

class ExampleHash
{
    public static function decodeQueriesTable()
    {
        if (file_exists('../cache/data-hash.txt')) {
            $hashFileContent = file_get_contents('../cache/data-hash.txt');

            $explodeData = explode("&&", $hashFileContent);

            $original_plaintext = openssl_decrypt($explodeData[0], $explodeData[1], $explodeData[2], $options=0, $explodeData[3], $explodeData[4]);

            return ($original_plaintext);
        } else {
            throw new exception('Unable to open main hash data from disk.');
        }
    }
    public static function encodeQueriesTable()
    {
//        $hashKey = 'cbc9bff742e9aeeaaf1d18dd5f15ec2b084c0d7710d39d517376817d0706c041';
        $dataFromApi = JsqlService::callJsqlApi(null, 'GET', 'queries');

        $hashKey = openssl_random_pseudo_bytes(32);
        $cipher = "aes-128-gcm";
        $plaintext = json_encode($dataFromApi["data"]);

        if (in_array($cipher, openssl_get_cipher_methods()))
        {
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $ciphertext = openssl_encrypt($plaintext, $cipher, $hashKey, $options=0, $iv, $tag);

            $fp = fopen('../cache/data-hash.txt', 'w'); //open cache file
            fwrite($fp, $ciphertext); //create new cache file
            fwrite($fp, '&&'); //create new cache file
            fwrite($fp, $cipher); //create new cache file
            fwrite($fp, '&&'); //create new cache file
            fwrite($fp, $hashKey); //create new cache file
            fwrite($fp, '&&'); //create new cache file
            fwrite($fp, $iv); //create new cache file
            fwrite($fp, '&&'); //create new cache file
            fwrite($fp, $tag); //create new cache file
            fclose($fp); //close cache file


//            var_dump("ciper");
//            var_dump($cipher);
//            var_dump("iv");
//            var_dump($iv);
//            var_dump("tag");
//            var_dump($tag);
        }

    }
}