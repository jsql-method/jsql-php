<?php
include_once '../jsql-libs/Database.php';
include_once '../jsql-libs/JsqlService.php';

class JsqlController
{
    public static function select($dataWithTokens, $params = [])
    {
        // EysppemBMi2vF2IuwcNmQ
//        try {
            $dbResult = [];
            $newArray = [];
            $conn = new Database();

        JsqlService::$arrayOfCachedOptions['saltBefore'] = '1';
        // in future version 'saltBefore' key will be replaced by 'prod' key
        if ((boolean)JsqlService::$arrayOfCachedOptions['saltBefore']) {
            //in future version encoding will be replaced by hitting to API endpoint which return encode data
            if (!file_exists('../cache/data-hash.txt')) {
                (ExampleHash::encodeQueriesTable());
                var_dump("tworzy");
            }


            ($jsonString = ExampleHash::decodeQueriesTable());
            $array = (json_decode($jsonString, true));

            $count = count($array);

            foreach ($dataWithTokens as $k=>$v) {

                for ($i=0; $i < $count; $i++) {

                    if ($array[$i]['hash'] === $v) {
                        $newArray[] = $array[$i];
                    }

                }

            }
var_dump("Z PLIKU");
            $result = $newArray;

        } else {
            // s[prawdzic czy istnieje jak istnieje to usunac plik
            if (file_exists('../cache/data-hash.txt')) {
                unlink('../cache/data-hash.txt');
            }
            $data = JsqlService::transformDataToString($dataWithTokens);
            ($result = JsqlService::callJsqlApi($data, 'POST', 'queries'));
            var_dump("z chmury");
        }

            $gluedTokens = JsqlService::glueTokensToString($result);
//        var_dump($gluedTokens);

            if (strchr($gluedTokens,";")) {
                $queriesArray = explode(";", $gluedTokens);

                foreach($queriesArray as $item) {
//                    try {
                        if (!empty($item)) {
                            $dbResult[] = $conn->select(trim($item, " "), $params);
                        }
//                    }catch(\Exception $e) {
//                        return response()->json([
//                            'message' => $e->getMessage(),
//                        ], 400);
//                    }
                }
            } else {
                $dbResult[] = $conn->select($gluedTokens, $params);
            }

//        }catch(BadResponseException $e) {
//            $response = json_decode($e->getResponse()->getBody()->getContents(), true);
//            return response()->json([
//                'message' => $response['message'],
//            ], 400);
//        }
        $conn = null;

        return $dbResult;
    }

    public static function insert($data, $params = [])
    {
        // SfPwculIWwFsSrbmLboeQ
        $dbResult = [];
        $conn = new Database();

        $result = JsqlService::callJsqlApi($data, 'POST', 'queries');

        $gluedTokens = JsqlService::glueTokensToString($result);

        if (strchr($gluedTokens,";")) {
            $queriesArray = explode(";", $gluedTokens);

            foreach($queriesArray as $item) {

                if (!empty($item)) {
                    $lastId = $conn->insert(trim($item, " "), $params);

                    $dbResult[] = json_encode(
                        [[
                            "lastId" => $lastId
                        ]]
                    );
                }
            }
        } else {
            $lastId = $conn->insert($gluedTokens, $params);
            $dbResult = json_encode(
                [[
                    "lastId" => $lastId
                ]]
            );
        }

        $conn = null;

        return $dbResult;
    }

    public static function delete($data, $params = [])
    {
        // 9JHQwwozZfdrmcuweRYnA
        $dbResult = [];
        $conn = new Database();

        $result = JsqlService::callJsqlApi($data, 'POST', 'queries');

        $gluedTokens = JsqlService::glueTokensToString($result);

        if (strchr($gluedTokens,";")) {
            $queriesArray = explode(";", $gluedTokens);

            foreach($queriesArray as $item) {

                if (!empty($item)) {
                    $status = $conn->delete(trim($item, " "), $params);

                    $dbResult[] = json_encode(
                        [[
                            "status" => $status
                        ]]
                    );
                }
            }
        } else {
            $status = $conn->delete($gluedTokens, $params);
            $dbResult = json_encode(
                [[
                    "status" => $status
                ]]
            );
        }

        $conn = null;

        return $dbResult;
    }

    public static function update($data, $params = [])
    {
        // ryXsGU9eJhlkZjYDNq68Q
        $dbResult = [];
        $conn = new Database();

        $result = JsqlService::callJsqlApi($data, 'POST', 'queries');

        $gluedTokens = JsqlService::glueTokensToString($result);

        if (strchr($gluedTokens,";")) {
            $queriesArray = explode(";", $gluedTokens);

            foreach($queriesArray as $item) {

                if (!empty($item)) {
                    $status = $conn->update(trim($item, " "), $params);

                    $dbResult[] = json_encode(
                        [[
                            "status" => $status
                        ]]
                    );
                }
            }
        } else {
            $status = $conn->update($gluedTokens, $params);
            $dbResult = json_encode(
                [[
                    "status" => $status
                ]]
            );
        }

        $conn = null;

        return $dbResult;
    }
}