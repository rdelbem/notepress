<?php

use Codeception\TestCase\WPTestCase;
use Olmec\OlmecNotepress\Activation;

class ActivationTest extends WPTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!defined('Olmec\OlmecNotepress\OLMEC_NOTEPRESS_ABSPATH') || !defined('OLMEC_NOTEPRESS_ABSPATH')) {
            define('Olmec\OlmecNotepress\OLMEC_NOTEPRESS_ABSPATH', WP_PLUGIN_DIR . '/olmec-notepress');
            define('OLMEC_NOTEPRESS_ABSPATH', WP_PLUGIN_DIR . '/olmec-notepress');
        }
    }

    public function testRun()
    {
        Activation::run();

        $page = get_page_by_path('notepress');
        $this->assertNotNull($page);
        $this->assertEquals('Base Page Route', $page->post_title);

        $permalinkStructure = get_option('permalink_structure');
        $this->assertEquals('/%postname%/', $permalinkStructure);

        $currentUserId = wp_get_current_user()->ID;
        $storedOwner = get_option('notepress_owner');
        $this->assertEquals($currentUserId, $storedOwner);
    }
}
