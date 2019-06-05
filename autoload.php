<?php

// Libs
require_once 'config/Config.php';
require_once 'router/Router.php';
require_once 'router/Handler.php';

// Namespaces
use JSQL\Config;
use JSQL\Router;
use JSQL\Handler;

// Global config class
$__CONFIG = new Config('dev');

// Routes config
$__ROUTE_PREFIX = '/api/jsql/';

/**
 * Error handle: not allowed
 */
Router::e_methodNotAllowed( function() {
    header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);
    header('Content-Type: application/json');
    echo json_encode(
        [
            "code" => 405,
            "description" => "Method Not Allowed"
        ], JSON_PRETTY_PRINT
    );

    die();
});

/**
 * Error handle: not found
 */
Router::e_pathNotFound( function() {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
    header('Content-Type: application/json');
    echo json_encode(
        [
            "code" => 404,
            "description" => "Not Found"
        ], JSON_PRETTY_PRINT
    );

    die();
});

/**
 * Routes generation
 */
foreach (['select', 'delete', 'update', 'insert', 'rollback', 'commit'] as $path) {
    // Append new route
    Router::add($__ROUTE_PREFIX . $path, function() use ($__CONFIG, $path) {
        Handler::request_post(
            $path,
            $__CONFIG['KEYS']
        );
    }, 'post');
}
