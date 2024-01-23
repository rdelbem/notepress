<?php

/**
 * This file is the only place where we define new API routes.
 *
 * -> example of open api route (new Route())->create('POST', '/route-name', fn() => ..., true), last argument implicates it is an open route
 * -> example of internal route (new Route())->create('POST', '/route-name', fn() => ...), nonce checked
 */

namespace Olmec\OlmecNotepress;

if (!defined('ABSPATH')) {
    exit;
}

use Olmec\OlmecNotepress\Api\Notes;
use Olmec\OlmecNotepress\Api\Route;

$route = new Route();
$notes = new Notes();

// uncomment this line and visit yoursiteurl/wp-json/API_NAME_SPACE/test
// $route->create('GET', '/test', fn() => wp_send_json('hello world'), true);
// uncomment this line and visit yoursiteurl/wp-json/API_NAME_SPACE/test/100, you should see 100 
// $route->create('GET', '/test/:id', fn($params) => wp_send_json($params['id']), true);

// notes routes
$route->create('GET', '/notes', fn(\WP_REST_Request $request) => $notes->getAll($request->get_param('page') ?? 1));
$route->create('GET', '/notes/:id', fn(\WP_REST_Request  $params) => $notes->getById((int) $params['id']));
$route->create('POST', '/notes', fn(\WP_REST_Request $params) => $notes->create($params));
$route->create('PATCH', '/notes/:id', fn(\WP_REST_Request $params) => $notes->update($params));
$route->create('DELETE', '/notes/:id', fn(\WP_REST_Request  $params) => $notes->delete((int) $params['id']));
$route->create('GET', '/notes/search', fn(\WP_REST_Request $request) => $notes->search($request->get_param('query'), $request->get_param('page') ?? 1));

// taxonomy routes

