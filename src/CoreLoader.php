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
        add_filter('page_template', [$this, 'themePageTemplate']);
        // adds support to react native routing system
        add_action('init', [$this, 'reactRoutingSupport']);
        // adds an admin navbar link to the app
        add_action('admin_bar_menu', [$this, 'adminNavBarLink'], 1000);
        // load app
        $this->loadReactApp();
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
     * to the correct temaplate
     *
     * @param string $template
     * @return string
     */
    function themePageTemplate($template): string
    {
        global $post;
        if (OLMEC_NOTEPRESS_TEMPLATE_PATH === get_post_meta($post->ID, '_wp_page_template', true)) {
            $template = OLMEC_NOTEPRESS_TEMPLATE_PATH;
        }
        return $template;
    }

    /**
     * This will make possible to use
     * react routing system without
     * causing navigation issues
     *
     * @return void
     */
    function reactRoutingSupport(): void 
    {
        add_rewrite_rule('^notepress/(.+)?', 'index.php?pagename=notepress', 'top');
    }

    /**
     * Adds a simple navbar link to the app main route
     *
     * @param \WP_Admin_Bar $wpNavBarNodes
     * @return void
     */
    function adminNavBarLink(\WP_Admin_Bar $wpNavBarNodes): void {
        $args = [
            'id' => 'notepress-navbar-link',
            'title' => esc_html('Go to Notepress'),
            'href' => OLMEC_NOTEPRESS_APP_LINK,
        ];

        $wpNavBarNodes->add_node($args);

        if(!is_admin()){
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
    }

    function loadReactApp(): void {
        $this->loadScriptDynamically(
            'app',
            OLMEC_NOTEPRESS_REACT_APP_URL,
            ['wp-element'],
            time(), // TODO: use env vars
            false,
            $this->isCurrentLocationNotepressDomain()
        );
    }
}
