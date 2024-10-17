<?php

namespace Olmec\OlmecNotepress;

use Olmec\OlmecNotepress\Indexation;

if (!defined('ABSPATH')) {
    exit;
}

final class CoreLoader
{
    use \Olmec\OlmecNotepress\Util\CurrentLocation;
    use \Olmec\OlmecNotepress\Util\DynamicScriptLoader;

    function __construct()
    {
        // the notepress owner is the one user that activated the plugin
        // most likely the person that will use it
        if ((int) get_option('notepress_owner') !== get_current_user_id()) {
            return;
        }
        // takes control over traditional wp routes
        $this->takeOverControl();
        // register our base template file for our custom page created on activation
        $this->themePageTemplate();
        // adds support to react native routing system
        $this->reactRoutingSupport();
        // adds an admin navbar link to the app
        $this->adminNavBarLink();
        // load app
        $this->loadReactApp();
        // remove admin nav bar from not admin routes
        $this->removeAdminNavbar();

        new Indexation();
    }

    /**
     * Takes control over all routes
     * Checks if the user is logged in or not
     * Redirects to login page
     *
     * @return void
     */
    function takeOverControl(): void
    {
        // WP_CLI won´t work if we take over control
        if (defined('WP_CLI') && WP_CLI) {
            return;
        }

        if (!is_user_logged_in() && !is_login()) {
            wp_redirect(wp_login_url());
            exit;
        }

        if (is_user_logged_in() && !is_login() && !is_admin() && !$this->isCurrentLocationNotepressDomain()) {
            wp_redirect(home_url() . '/notepress');
            exit;
        }
    }

    /**
     * Associates the visited route 
     * to the correct template
     * 
     * @return void
     */
    function themePageTemplate(): void
    {
        add_filter('page_template', function (string $template): string {
            global $post;
            if (OLMEC_NOTEPRESS_TEMPLATE_PATH === get_post_meta($post->ID, '_wp_page_template', true)) {
                $template = OLMEC_NOTEPRESS_TEMPLATE_PATH;
            }
            return $template;
        });
    }

    /**
     * This will make possible to use
     * react routing system without
     * colliding with WordPress routing
     *
     * @return void
     */
    function reactRoutingSupport(): void
    {
        // Forces WP to understand sub-routes
        add_action('parse_request', function ($wp) {
            if (preg_match('#^notepress/(.+)#', $wp->request)) {
                status_header(200);
                include (get_index_template());
                exit;
            }
        });

        add_action(
            'init',
            fn() => add_rewrite_rule(
                '^notepress/(.+)?',
                'index.php?pagename=notepress',
                'top'
            )
        );
    }

    /**
     * Adds a simple navbar link to the app main route
     * 
     * @return void
     */
    function adminNavBarLink(): void
    {
        add_action('admin_bar_menu', function (\WP_Admin_Bar $wpNavBarNodes) {
            $args = [
                'id' => 'notepress-navbar-link',
                'title' => esc_html('Go to Notepress'),
                'href' => OLMEC_NOTEPRESS_APP_LINK,
            ];

            $wpNavBarNodes->add_node($args);

            if (!is_admin()) {
                $nodesToRemove = [
                    'comments',
                    'new-content',
                    'view-site',
                    'edit',
                    'customize',
                    'updates',
                    'theme-dashboard',
                    'site-editor',
                    'notepress-navbar-link'
                ];
                foreach ($nodesToRemove as $node) {
                    $wpNavBarNodes->remove_node($node);
                }
            }
        }, 1000);

    }

    function loadReactApp(): void
    {
        $this->loadScriptDynamically(
            'app',
            OLMEC_NOTEPRESS_REACT_APP_URL,
            ['wp-element'],
            time(), // TODO: use env vars
            false,
            $this->isCurrentLocationNotepressDomain()
        );
    }

    function removeAdminNavbar(): void
    {
        add_filter('show_admin_bar', fn() => is_admin());
    }
}
