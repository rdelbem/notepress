<?php

/**
 * This file is the only place where we define new API routes.
 *
 * Internal or external routes have to be defined here.
 *
 * If a the route is internal, i.e a call from the admin to the admin
 * it is necessary to explicitily say it has no jwt, it only uses a wp nonce
 * as means to validation.
 *
 * -> example of open api route (new Route())->create('POST', '/route-name', fn() => ..., true), last argument implicates it is an open route
 * -> example of internal route (new Route())->create('POST', '/route-name', fn() => ...), nonce checked
 */

namespace Olmec\OlmecNotepress;

use Olmec\OlmecNotepress\Api\Notes;
use Olmec\OlmecNotepress\Api\Route;

$route = new Route();
$notes = new Notes();

// uncomment this line and visit yoursiteurl/wp-json/API_NAME_SPACE/test
// $route->create('GET', '/test', fn() => wp_send_json('hello world'), true);
// uncomment this line and visit yoursiteurl/wp-json/API_NAME_SPACE/test/100, you should see 100 
// $route->create('GET', '/test/:id', fn($params) => wp_send_json($params['id']), true);

// notes routes
$route->create('GET', '/notes', fn() => $notes->getAll());
$route->create('GET', '/notes/:id', fn(\WP_REST_Request  $params) => $notes->getById($params['id']));
$route->create('POST', '/notes', fn(\WP_REST_Request $params) => $notes->create($params));
$route->create('PATCH', '/notes/:id', fn(\WP_REST_Request $params) => $notes->update($params));
$route->create('DELETE', '/notes/:id', fn(\WP_REST_Request  $params) => $notes->delete((int) $params['id']));

// taxonomy routes

