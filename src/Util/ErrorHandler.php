<?php

namespace Olmec\OlmecNotepress\Util;

if (!defined('ABSPATH')) {
    exit;
}

use \WP_Error;

/**
 * Handles errors
 */
trait ErrorHandler
{
    function setError(string $errorMsg): void {
        error_log($errorMsg);
        new WP_Error($errorMsg);
        exit;
    }
}