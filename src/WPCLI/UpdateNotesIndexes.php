<?php

namespace Olmec\OlmecNotepress\WPCLI;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Updates the indexing count of notes by workspace.
 */
class UpdateNotesIndexes extends \WP_CLI_Command
{

    /**
     * Updates the indexing count of notes by workspace.
     *
     * ## EXAMPLES
     *
     *    wp update-workspaces-notes-count
     *
     */
    public function __invoke($args)
    {
        $workspaces = get_terms(['taxonomy' => 'workspaces', 'hide_empty' => false]);
        $totalNotesCount = 0;

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
                        'include_children' => false
                    ],
                ],
                'posts_per_page' => -1
            ];
            $query = new \WP_Query($args);
            $count = $query->found_posts;
            $totalNotesCount += $count;

            update_option($workspace->name, $count);
            class_exists('WP_CLI') && \WP_CLI::success(sprintf('Workspace "%s" updated with %d notes.', $workspace->name, $count));
        }

        update_option('total_notes', $totalNotesCount);
        class_exists('WP_CLI') && \WP_CLI::success(sprintf('Total general notes count updated to %d.', $totalNotesCount));
    }
}