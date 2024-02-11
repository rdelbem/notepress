<?php

namespace Olmec\OlmecNotepress;
use Olmec\OlmecNotepress\Util\CreatePostType;
use Olmec\OlmecNotepress\Util\CreateTaxonomy;

if (!defined('ABSPATH')) {
    exit;
}

final class PostTypeAndTaxonomy
{   
    use CreatePostType, CreateTaxonomy;

    function __construct($postType, $taxonomy) {
        $this->registerCustomTaxonomy('workspaces', 'notes', $taxonomy);
        $this->registerCustomPostType('notes', $postType);
    }
}
