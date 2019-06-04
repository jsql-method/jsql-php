<?php

// Debug config
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Libs
require_once 'config/Config.php';
require_once 'router/Router.php';
require_once 'router/Handler.php';

use JSQL\Config;
use JSQL\Router;
use JSQL\Handler;

// Global config class
$__CONFIG = new Config('dev');

// Routes config
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
 * Select
 */

Router::add('/select', function() use ($__CONFIG) {
    Handler::request_post(
        'select',
        $__CONFIG['KEYS']
    );
}, 'post');

/**
 * Delete
 */
Router::add('/delete', function() use ($__CONFIG) {
    Handler::request_post(
        'delete',
        $__CONFIG['KEYS']
    );
}, 'post');

/**
 * Update
 */
Router::add('/update', function() use ($__CONFIG) {
    Handler::request_post(
        'update',
        $__CONFIG['KEYS']
    );
}, 'post');

/**
 * Insert
 */
Router::add('/insert', function() use ($__CONFIG) {
    Handler::request_post(
        'insert',
        $__CONFIG['KEYS']
    );
}, 'post');

/**
 * Rollback
 */
Router::add('/rollback', function() use ($__CONFIG) {
    Handler::request_post(
        'rollback',
        $__CONFIG['KEYS']
    );
}, 'post');

/**
 * Commit
 */
Router::add('/commit', function() use ($__CONFIG) {
    Handler::request_post(
        'commit',
        $__CONFIG['KEYS']
    );
}, 'post');
