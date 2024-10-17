<?php

namespace Olmec\OlmecNotepress;

if (!defined('ABSPATH')) {
    exit;
}

define('OLMEC_NOTEPRESS_VERSION', '1.0.0');
define('OLMEC_NOTEPRESS_API_NAMESPACE', 'notepress-api');
define('OLMEC_NOTEPRESS_TEMPLATE_PATH', plugin_dir_path(__FILE__) . 'AppRootView/base-root-template.php');
define('OLMEC_NOTEPRESS_APP_LINK', home_url() . '/notepress');
define('OLMEC_NOTEPRESS_DOMAIN', 'olmec-notepress-domain');
define('OLMEC_NOTEPRESS_REACT_APP_URL', plugin_dir_url(__DIR__) . 'assets/js/prod/App.js');
define('OLMEC_NOTEPRESS_TAXONOMY_NAME', 'workspaces');
define('OLMEC_NOTEPRESS_API_URL', home_url() . '/wp-json/' . OLMEC_NOTEPRESS_API_NAMESPACE);
define('OLMEC_NOTEPRESS_ABSPATH', WP_PLUGIN_DIR . '/olmec-notepress');