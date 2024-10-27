<?php
/**
 * Plugin Name: Notepress OLMC
 * Plugin URI: http://delbem.net/portfolio/notepress
 * Description: Transforms your WP instance in a note taking PWA app
 * Version: 1.0.0
 * Author: Olmec, Rodrigo Vieira Del Bem
 * Author URI: http://delbem.net/portfolio
 * License: GPLv2 or later
 * Text Domain: notepress-olmc
 * Domain Path: /languages
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// loads composer autoload
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/routes.php';
require_once __DIR__ . '/src/defines.php';
require_once __DIR__ . '/src/notepressPostypeAndTaxonomyDefinition.php';
require_once __DIR__ . '/src/Util/functions.php';

use Olmec\OlmecNotepress\Activation;
use Olmec\OlmecNotepress\CoreLoader;
use Olmec\OlmecNotepress\WPCLI\NotepressCLI;
use Olmec\OlmecNotepress\PostTypeAndTaxonomy;
use function Olmec\OlmecNotepress\Util\registerUserLogin;

register_activation_hook(__FILE__, fn() => Activation::run());

if (!class_exists('CoreLoader')) {
    add_action('plugins_loaded', fn() => new CoreLoader());
    // create posttype and taxonomy
    new PostTypeAndTaxonomy(OLMEC_NOTEPRESS_POSTTYPE, OLMEC_NOTEPRESS_TAXONOMY);
    // adds Notepress CLI commands
    (new NotepressCLI())->createCommands();
    // register the time a user logged in for the last time
    // due to the nature of the "wp_login" hook, this cannot be called elsewhere
    registerUserLogin();
}
