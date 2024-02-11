<?php

namespace Olmec\OlmecNotepress\Api;

if (!defined('ABSPATH')) {
    exit;
}

use \WP_REST_Server;
use \WP_REST_Request;

final class Route
{

    /**
     * Performes a security check on the incoming request header
     * If it has a wp nonce it is an internal call from the admin
     * If it has a pub key then it is an external call
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function securityCheck(WP_REST_Request $request)
    {
        $headers = $request->get_headers();
        $nonce = $headers['x_wp_nonce'][0] ?? null;
        $isValidNonce = $nonce ? wp_verify_nonce($nonce, 'wp_rest') : false;

        if ($isValidNonce) {
            return current_user_can('publish_posts');
        }

        return false;
    }

    /**
     * Normalize http verbs to have valid WordPress Server names
     *
     * @param string $verbName
     * @return string | null
     */
    public function getHttpVerb(string $verbName)
    {
        switch ($verbName) {
            case 'POST':
                return WP_REST_Server::CREATABLE;
            case 'PATCH':
                return WP_REST_Server::EDITABLE;
            case 'GET':
                return WP_REST_Server::READABLE;
            case 'DELETE':
                return WP_REST_Server::DELETABLE;
        }

        return null;
    }

    /**
     * Use this function to create a new api route.
     * Simple write (new Route())->create('POST', '/route-name', fn() => ...)
     * 
     * The last argument determines if JWT will be evaluated. By default JWT is required
     * You have to set it to false in order to disable JWT validation. 
     *
     * @param string $httpVerb POST, PATCH, GET, DELETE
     * @param string $route
     * @param callable $callback
     * @param bool $openApi
     * @return bool because add_action always returns true
     */
    public function create(string $httpVerb, string $route, callable $callback, bool $openApi = false)
    {
        $routeHasParams = str_contains($route, ':');
        $routeArray = $routeHasParams && (bool) explode(':', $route) ? explode(':', $route) : [];

        if($routeHasParams && count($routeArray) > 2) {
            wp_send_json('Too many args');
            exit;
        }

        if($routeHasParams && count($routeArray) > 1) {
            $route = '/' . $routeArray[0] . '(?P<' . $routeArray[1] . '>\d+)';
        }

        return add_action(
            'rest_api_init',
            fn(): bool => register_rest_route(
                OLMEC_NOTEPRESS_API_NAMESPACE,
                $route,
                [
                    'methods' => $this->getHttpVerb($httpVerb),
                    'callback' => $callback,
                    'permission_callback' => !$openApi ? [$this, 'securityCheck'] : '__return_true'
                ]
            )
        );
    }
}