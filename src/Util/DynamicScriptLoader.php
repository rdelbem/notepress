<?php

namespace Olmec\OlmecNotepress\Util;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Simplify to one call the loading of a script asset
 * 
 * Usage goes like:  $this->loadScriptDynamically(
 *      'script-name', 
 *      'script-path.js', 
 *      '[deps]', 
 *      'load at footer',
 *      'version',
 *      'condition to be eval');
 */
trait DynamicScriptLoader
{

    /**
     * Register and enqueue a sript, accepts a condition as the last arg, default to true
     *
     * @param string $scriptName
     * @param string $scriptPath
     * @param array $dependencies
     * @param string $version
     * @param boolean $inFooter
     * @param boolean $condition
     * @return void
     */
    public function loadScriptDynamically(
        string $scriptName,
        string $scriptPath,
        array $dependencies,
        string $version,
        bool $inFooter,
        bool $condition = true
    ) {
        add_action('wp_enqueue_scripts', function () use ($scriptName, $scriptPath, $dependencies, $version, $inFooter, $condition) {
            wp_register_script($scriptName, $scriptPath, $dependencies, $version, $inFooter);

            if ($condition) {
                wp_enqueue_script($scriptName);
            }
        });
    }

}