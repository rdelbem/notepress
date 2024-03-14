<?php

namespace Olmec\OlmecNotepress;

if (!defined('ABSPATH')) {
    exit;
}

class CoreLoader
{
    use \Olmec\OlmecNotepress\Util\CurrentLocation;
    use \Olmec\OlmecNotepress\Util\DynamicScriptLoader;

    function __construct()
    {
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
        // total workspace counter
        $this->countWorkspaces();
        // total notes
        $this->countNotes();
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
        add_action('parse_request', function($wp) {
            if (preg_match('#^notepress/(.+)#', $wp->request)) {
                status_header(200);
                include(get_index_template());
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
        add_filter('show_admin_bar', fn () => is_admin());
    }

    function countWorkspaces(): void {
        $update = fn () => update_option('total_workspaces', get_terms([
            'taxonomy' => 'workspaces',
            'hide_empty' => false,
            'fields' => 'count',
        ]));

        add_action('created_workspaces', $update);
        add_action('delete_workspaces', $update);
    }

    function countNotes() {
        add_action('save_post_notes', function($postId, $post, $update){
            if ($post->post_type !== 'notes') {
                return;
            }
        
            $totalNotes = (int) get_option('total_notes', 0);
            if (!$update) {
                update_option('total_notes', $totalNotes + 1);
            }
        }, 10, 3);

        add_action('before_delete_post', function($postId){
            $postType = get_post_type($postId);
            if ($postType !== 'notes') {
                return;
            }
        
            $totalNotes = (int) get_option('total_notes', 0);
            update_option('total_notes', max(0, $totalNotes - 1));
        }, 10, 1);
    }
}
