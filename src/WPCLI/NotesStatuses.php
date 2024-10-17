<?php

namespace Olmec\OlmecNotepress\WPCLI;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Counts the posts of type 'notes' organizad by 'workspaces'.
 */
class NotesStatuses extends \WP_CLI_Command
{

    /**
     * Counts the posts of type 'notes' organizad by 'workspaces'.
     *
     * ## EXAMPLES
     *
     *  $ wp notes-workspaces count
     *
     */
    public function count($args, $assoc_args)
    {
        $workspaces = get_terms(['taxonomy' => 'workspaces', 'hide_empty' => false]);
        if (empty ($workspaces)) {
            class_exists('WP_CLI') && \WP_CLI::error('No workspace found.');
            return;
        }

        foreach ($workspaces as $workspace) {
            $args = [
                'post_type' => 'notes',
                'tax_query' => [
                    [
                        'taxonomy' => 'workspaces',
                        'field' => 'term_id',
                        'terms' => $workspace->term_id,
                    ],
                ],
            ];
            $query = new \WP_Query($args);
            class_exists('WP_CLI') && \WP_CLI::line(sprintf('Workspace "%s" has %d notes.', $workspace->name, $query->found_posts));
        }
    }
}
