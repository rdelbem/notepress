<?php

namespace Olmec\OlmecNotepress;

if (!defined('ABSPATH')) {
    exit;
}

class CoreLoader
{
    use \Olmec\OlmecNotepress\Util\CurrentLocation;

    function __construct()
    {
        // takes control over traditional wp routes
        $this->takeOverControl();
        // register our base template file for our custom page created on activation
        add_filter('page_template', [$this, 'themePageTemplate']);
        // adds support to react native routing system
        add_action('init', [$this, 'reactRoutingSupport']);
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

        if (is_user_logged_in() && !is_login() && !is_admin() && !(strpos($this->CurrentLocationIs(), '/notepress') !== false)) {
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
    function themePageTemplate($template)
    {
        global $post;
        $expectTemplatePath = plugin_dir_path(__FILE__) . 'AppRootView/base-root-template.php';
        if ($expectTemplatePath === get_post_meta($post->ID, '_wp_page_template', true)) {
            $template = $expectTemplatePath;
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
    function reactRoutingSupport() {
        add_rewrite_rule('^notepress/(.+)?', 'index.php?pagename=notepress', 'top');
    }
}
