<?php

namespace Olmec\OlmecNotepress\Util;

if (!defined('ABSPATH')) {
    exit;
}

use Dotenv\Dotenv;

/**
 * Loads environment variables in the root folder
 */
trait LoadEnvVars
{
    function loadEnvVars(): void {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }
}