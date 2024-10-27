<?php

namespace Olmec\OlmecNotepress;

if (!defined('ABSPATH')) {
    exit;
}

final class Activation
{
    public static function run()
    {
        self::createBasePageRoute();
        self::updatePermalinkStructure();
        self::registerOwnerUser();
        self::generateEnvHashKey();

        (new Auth())->createSession(wp_get_current_user());
    }

    protected static function createBasePageRoute()
    {
        $pageTitle = 'Base Page Route';
        $pageSlug = 'notepress';

        $args = [
            'post_type' => 'page',
            'post_status' => 'any',
            'title' => $pageTitle,
            'numberposts' => 1
        ];

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }

        $pageDetails = [
            'post_type' => 'page',
            'post_title' => $pageTitle,
            'post_status' => 'publish',
            'post_name' => $pageSlug,
            'post_author' => 1,
        ];

        $pageId = wp_insert_post($pageDetails);

        if ($pageId != 0) {
            update_post_meta($pageId, '_wp_page_template', plugin_dir_path(__FILE__) . 'AppRootView/base-root-template.php');
        }

        return $pageId;
    }

    protected static function updatePermalinkStructure()
    {
        $intendedStructure = '/%postname%/';
        update_option('permalink_structure', $intendedStructure);
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure($intendedStructure);
        $wp_rewrite->flush_rules();
    }

    protected static function registerOwnerUser(): void
    {
        update_option('notepress_owner', (wp_get_current_user())->ID);
        update_option('notepress_owner_id', uniqid() . '#' . (wp_get_current_user())->ID);
    }

    protected static function generateEnvHashKey() {
        $path = OLMEC_NOTEPRESS_ABSPATH . '/.env';
        if (!file_exists($path)) {
            $hashKey = bin2hex(random_bytes(256));
            $envKey = "JWT_HASH_KEY={$hashKey}\n";
            file_put_contents($path, $envKey, LOCK_EX);
        }
    }
}