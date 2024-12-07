<?php
namespace Olmec\OlmecNotepress\WPCLI;

use Olmec\OlmecNotepress\WPCLI\NotesStatuses;
use Olmec\OlmecNotepress\WPCLI\UpdateNotesIndexes;
use Olmec\OlmecNotepress\WPCLI\UpdateNotesInWorkspacesOption;

if(!defined('ABSPATH') && !defined('WP_CLI')){
    exit;
}

$notePressCommands = [
    'notes-workspaces' => NotesStatuses::class,
    'update-workspaces-notes-count' => UpdateNotesIndexes::class,
    'update-notes-in-workspaces-option' => UpdateNotesInWorkspacesOption::class
];