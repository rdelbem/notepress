<?php

namespace Olmec\OlmecNotepress\Util;

if (!defined('ABSPATH')) {
    exit;
}

trait CreatePostType {
    /**
     * Registers a custom post type.
     *
     * @param string $postType The post type key.
     * @param array $args Arguments to define the post type.
     */
    public function registerCustomPostType(string $postType, array $args) {
        add_action('init', function() use ($postType, $args) {
            register_post_type($postType, $args);
        });
    }
}