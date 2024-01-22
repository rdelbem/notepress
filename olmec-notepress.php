<?php

/**
 * Plugin Name: Notepress
 * Plugin URI: http://delbem.net/portfolio/notepress
 * Description: Transforms your WP instance in a note taking PWA app
 * Version: 1.0.0
 * Author: Olmec, Rodrigo Vieira Del Bem
 * Author URI: http://delbem.net/portfolio
 * License: GPL-2.0+
 * Text Domain: olmec-notepress-domain
 * Domain Path: languages
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// loads composer autoload
require_once __DIR__ . '/vendor/autoload.php';

use Olmec\OlmecNotepress\Activation;
use Olmec\OlmecNotepress\CoreLoader;
use Olmec\OlmecNotepress\PostTypeAndTaxonomy;

register_activation_hook(__FILE__, fn() => Activation::run());

if(!class_exists('CoreLoader')){
    add_action('init', fn() => new CoreLoader());
    // create posttype and taxonomy
    new PostTypeAndTaxonomy(OLMEC_NOTEPRESS_POSTTYPE, OLMEC_NOTEPRESS_TAXONOMY);
}