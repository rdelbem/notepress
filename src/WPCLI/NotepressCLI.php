<?php

namespace Olmec\OlmecNotepress\WPCLI;

if(!defined('ABSPATH') && !defined('WP_CLI')){
    exit;
}

final class NotepressCLI
{
    function createCommands(): void {
        require_once __DIR__ . '/command-names.php';

        foreach ($notePressCommands as $command => $action) {
            class_exists('WP_CLI') && \WP_CLI::add_command($command, $action);
        }
    }
}
