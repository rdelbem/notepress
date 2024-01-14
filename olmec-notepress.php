<?php

/**
 * Plugin Name: Notepress
 * Plugin URI: http://delbem.net/portfolio/notepress
 * Description: Transforms your WP instance in a note taking app
 * Version: 1.0.0
 * Author: Rodrigo Vieira Del Bem
 * Author URI: http://delbem.net/portfolio
 * License: GPL-2.0+
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// loads composer autoload
require_once __DIR__ . '/vendor/autoload.php';

use Olmec\OlmecNotepress\CoreLoader;
use Olmec\OlmecNotepress\Activation;

register_activation_hook(__FILE__, fn() => Activation::run());

if(!class_exists('CoreLoader')){
    add_action('init', fn() => new CoreLoader());
}