<?php

/**
 * Plugin Name: Notepress OLMC
 * Plugin URI: https://delbem.net/projects/notepress/
 * Description: Transforms your WP instance in a note taking PWA app
 * Version: 1.0.0
 * Author: Rodrigo Vieira Del Bem <rodrigodelbem@gmail.com>
 * Author URI: https://delbem.net/
 * License: GPLv2 or later
 * Text Domain: notepress-olmc
 * Domain Path: /languages
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

if (!file_exists(plugin_dir_path(__FILE__) . '/.env')) {
    $path = plugin_dir_path(__FILE__) . '/.env';
    $hashKey = bin2hex(random_bytes(256));
    $envKey = "JWT_HASH_KEY={$hashKey}\n";

    try {
        file_put_contents($path, $envKey, LOCK_EX);
        error_log('.env successfully created with JWT_HASH_KEY.');
    } catch (\Exception $e) {
        error_log('Error creating .env file: ' . $e->getMessage());
    }
}

// loads composer autoload
require_once __DIR__ . '/vendor/autoload.php';

use Olmec\OlmecNotepress\Activation;
use Olmec\OlmecNotepress\Auth;
use Olmec\OlmecNotepress\CoreLoader;
use Olmec\OlmecNotepress\WPCLI\NotepressCLI;
use Olmec\OlmecNotepress\PostTypeAndTaxonomy;
use function Olmec\OlmecNotepress\Util\registerUserLogin;

register_activation_hook(__FILE__, function () {
    Activation::run();
    (new Auth())->createSession(wp_get_current_user());
});

if (!class_exists('CoreLoader')) {
    require_once __DIR__ . '/src/routes.php';
    require_once __DIR__ . '/src/defines.php';
    require_once __DIR__ . '/src/notepressPostypeAndTaxonomyDefinition.php';
    require_once __DIR__ . '/src/Util/functions.php';

    add_action('plugins_loaded', fn() => new CoreLoader());
    // create posttype and taxonomy
    new PostTypeAndTaxonomy(OLMEC_NOTEPRESS_POSTTYPE, OLMEC_NOTEPRESS_TAXONOMY);
    // adds Notepress CLI commands
    if (class_exists('NotepressCLI')) (new NotepressCLI())->createCommands();
    // register the time a user logged in for the last time
    // due to the nature of the "wp_login" hook, this cannot be called elsewhere
    registerUserLogin();
}
