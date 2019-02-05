<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers, User-Id, Auth-Token, Test-Local, Test-Remote');

include_once '../jsql-libs/JsqlController.php';

$arrayOfCachedOptions = [];
$paramsArray = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    JsqlService::initJsqlConfig();

    JsqlService::initJsqlCache();

    // Get data from
    $data = file_get_contents("php://input");

    if (!empty(json_decode($data, true)['params'])) {
        $paramsArray = json_decode($data, true)['params'];
    }

    $dataWithTokens = json_decode($data, true)['token'];
    $data = JsqlService::transformDataToString($dataWithTokens);

    $res = JsqlController::update($data, $paramsArray);

    echo $res;

    die();

//    echo  '{
//    "status":"success",
//    "statuscode":200,
//    "message":"Query updated correctly",
//    "data":[';
//    print_r(json_encode($conn->update($res[0]["query"])));
//    echo ']}';

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