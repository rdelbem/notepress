<?php

namespace Olmec\OlmecNotepress\Util;

if (!defined('ABSPATH')) {
    exit;
}

trait CreateTaxonomy {
    /**
     * Registers a custom taxonomy.
     *
     * @param string $taxonomy The taxonomy key.
     * @param array|string $objectType The object type or array of object types with which the taxonomy should be associated.
     * @param array $args Arguments to define the taxonomy.
     */
    public function registerCustomTaxonomy(string $taxonomy, $objectType, array $args) {
        add_action('init', function() use ($taxonomy, $objectType, $args) {
            register_taxonomy($taxonomy, $objectType, $args);
        });
    }
}