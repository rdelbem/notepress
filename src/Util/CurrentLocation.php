<?php

namespace Olmec\OlmecNotepress\Util;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Use this to get the current full url
 */
trait CurrentLocation
{
    function currentLocationIs()
    {
        if (defined('WP_CLI') && WP_CLI) {
            return;
        }
        
        if (
            isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
        ) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Use this to determine if the user is viewing a notepress domain
     *
     * @return boolean
     */
    function isCurrentLocationNotepressDomain(): bool {
        return strpos($this->currentLocationIs(), '/notepress') !== false;
    }
}
