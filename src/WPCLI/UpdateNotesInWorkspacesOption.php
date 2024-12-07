<?php

namespace Olmec\OlmecNotepress\WPCLI;

if (!defined('ABSPATH')) {
    exit;
}

class UpdateNotesInWorkspacesOption extends \WP_CLI_Command {

    /**
     * Updates options with serialized arrays of note IDs by workspace.
     *
     * ## EXAMPLES
     *
     *    wp update-notes-in-workspaces-option
     *
     */
    public function __invoke($args)
    {
        $workspaces = get_terms(['taxonomy' => 'workspaces', 'hide_empty' => false]);

        if (empty($workspaces)) {
            class_exists('WP_CLI') && \WP_CLI::error('No workspaces found.');
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
                'posts_per_page' => -1,
                'fields' => 'ids'
            ];
            $query = new \WP_Query($args);
            $noteIds = $query->posts;

            $optionName = 'notes_in_workspace_' . $workspace->term_id;

            update_option($optionName, $noteIds);
            class_exists('WP_CLI') && \WP_CLI::success(sprintf('Workspace "%s" (ID: %d) option updated with serialized note IDs array.', $workspace->name, $workspace->term_id));
        }
    }
}