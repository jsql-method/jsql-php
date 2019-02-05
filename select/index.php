<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers, User-Id, Auth-Token, Test-Local, Test-Remote');

include_once '../jsql-libs/JsqlController.php';
include_once '../jsql-libs/ExampleHash.php';
include_once '../jsql-libs/JsonToCsvClass.php';

$arrayOfCachedOptions = [];
$paramsArray = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    JsqlService::initJsqlConfig();

    JsqlService::initJsqlCache();

    // Get data from
    $data = file_get_contents("php://input");
    $jsonCorrectArray = JsqlService::isJsonCorrect($data);

    if ($jsonCorrectArray["isJsonCorrect"] === true)
    {
        if (array_key_exists('params',$jsonCorrectArray["JsonDecodedAssocArray"])) {
            $paramsArray = $jsonCorrectArray["JsonDecodedAssocArray"]['params'];
        }

        if (array_key_exists('token',$jsonCorrectArray["JsonDecodedAssocArray"])) {
            $dataWithTokens = $jsonCorrectArray["JsonDecodedAssocArray"]['token'];
            $res = JsqlController::select($dataWithTokens, $paramsArray);
        } else {
            $res = [[
                "description" => "Wrong key name 'token' or a typo in it!"
            ]];
        }
    } else {
        $res = [[
            "description" => $jsonCorrectArray
        ]];
    }

    if (JsqlService::is_multiArray($res)) {
        echo JsqlService::safe_json_encode($res);
    } else {
        echo JsqlService::safe_json_encode($res[0]);
    }

    die();

} else {
    header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);
    echo json_encode(
        [[
            "code" => 405,
            "description" => "Method Not Allowed!"
        ]], JSON_PRETTY_PRINT
    );

    die();
}