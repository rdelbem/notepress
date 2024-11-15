<?php

use Codeception\TestCase\WPTestCase;
use Olmec\OlmecNotepress\Api\Route;
use Olmec\OlmecNotepress\AuthInterface;

class RouteTest extends WPTestCase
{
    private $route;

    public function setUp(): void
    {
        parent::setUp();

        if (!defined('Olmec\OlmecNotepress\Api\OLMEC_NOTEPRESS_API_NAMESPACE')) {
            define('Olmec\OlmecNotepress\Api\OLMEC_NOTEPRESS_API_NAMESPACE', 'api_namespace');
        }

        $authInterfaceMock = $this->createMock(AuthInterface::class);
        $this->route = new Route($authInterfaceMock);
    }

    public function testCreateRoute()
    {
        $httpVerb = 'GET';
        $route = '/test-route';
        $callback = function () {
            return 'Hello World!';
        };

        $this->route->create($httpVerb, $route, $callback, true);

        $request = new WP_REST_Request($httpVerb, '/api_namespace' . $route);
        $response = rest_get_server()->dispatch($request);

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals('Hello World!', $response->get_data());
    }

    public function tearDown(): void
    {
        unset($this->route);
        parent::tearDown();
    }
}