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

use Olmec\OlmecNotepress\Auth;
use Olmec\OlmecNotepress\Api\Notes;
use Olmec\OlmecNotepress\Api\Route;
use Olmec\OlmecNotepress\Api\Workspaces;

$auth = new Auth();
$route = new Route($auth);
$notes = new Notes();
$workspaces = new Workspaces();

// uncomment this line and visit yoursiteurl/wp-json/API_NAME_SPACE/test
// $route->create('GET', '/test', fn() => wp_send_json('hello world'), true);
// uncomment this line and visit yoursiteurl/wp-json/API_NAME_SPACE/test/100, you should see 100 
// $route->create('GET', '/test/:id', fn($params) => wp_send_json($params['id']), true);

// notes routes
$route->create('GET', '/notes', fn(\WP_REST_Request $request) => $notes->getAll($request->get_param('page') ?? 1));
$route->create('GET', '/notes/:id', fn(\WP_REST_Request $params) => $notes->getById((int) $params['id']));
$route->create('POST', '/notes', fn(\WP_REST_Request $params) => $notes->create($params));
$route->create('PATCH', '/notes/:id', fn(\WP_REST_Request $params) => $notes->update($params));
$route->create('DELETE', '/notes/:id', fn(\WP_REST_Request $params) => $notes->delete((int) $params->get_param(key: 'id')));
$route->create('GET', '/notes/search', fn(\WP_REST_Request $request) => $notes->search($request->get_param('query'), (int) $request->get_param('page') ?? 1));
$route->create('GET', '/notes/workspace', fn(\WP_REST_Request $params) => $notes->getNotesByWorkspace($params->get_param('term'), (int) $params->get_param('page') ?? 1));

// workspaces routes
$route->create('GET', '/workspaces', fn(\WP_REST_Request $request) => $workspaces->getAll($request->get_param('page') ?? 1));
$route->create('GET', '/workspaces/:id', fn(\WP_REST_Request $params) => $workspaces->getById((int) $params['id']));
$route->create('POST', '/workspaces', fn(\WP_REST_Request $params) => $workspaces->create($params));
$route->create('PATCH', '/workspaces/:id', fn(\WP_REST_Request $params) => $workspaces->update($params));
$route->create('DELETE', '/workspaces/:id', fn(\WP_REST_Request $params) => $workspaces->delete((int) $params['id']));
$route->create('GET', '/workspaces/search', fn(\WP_REST_Request $request) => $workspaces->search($request->get_param('query'), $request->get_param('page') ?? 1));

// auth validation
$route->create('GET', '/auth/validate', fn(\WP_REST_Request $request) => $auth->apiValidateJwt($request)->getNewTokenOrNot(), true);