<?php

namespace Olmec\OlmecNotepress;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * These are the array of args that will
 * create and register postype and taxonomy
 * 
 * Change it here and anywhere else
 */

$olmecNotepressPosttypeDefinitions = [
    'labels' => [
        'name' => 'Notes',
        'singular_name' => 'Note'
    ],
    'public' => true,
    'has_archive' => true,
    'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
    'taxonomies'  => ['workspaces'],
];

$olmecNotepressTaxonomyDefinition = [
    'labels' => [
        'name' => 'Workspaces',
        'singular_name' => 'Workspace'
    ],
    'public' => true,
    'hierarchical' => true,
];

define('OLMEC_NOTEPRESS_POSTTYPE', $olmecNotepressPosttypeDefinitions);
define('OLMEC_NOTEPRESS_TAXONOMY', $olmecNotepressTaxonomyDefinition);