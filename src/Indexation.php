<?php

namespace Olmec\OlmecNotepress;

if (!defined('ABSPATH')) {
    exit;
}

use function Olmec\OlmecNotepress\Util\getNoteWorkspaces;

/**
 * This class takes care of indexing notes and workspaces
 * and notes by workspaces. This is necessary because we
 * can then avoid costy operations selection by tables join.
 * 
 * This decreases the response await for a api request done
 * by the front end, like the deletion of a workspace, and
 * consecutive deletion of all notes related to it.
 */
final class Indexation
{
    public function __construct() {
        $this->countNotes();
        $this->countWorkspaces();
        $this->indexNotesByWorkspace();
        $this->initiateWorkspaceIndexedOptions();
    }

    /**
     * Adds a total count of workspaces
     * so we can avoid getting the total
     * amount by a select operation in the
     * database.
     *
     * @return void
     */
    protected function countWorkspaces(): void {
        $update = fn () => update_option('total_workspaces', get_terms([
            'taxonomy' => 'workspaces',
            'hide_empty' => false,
            'fields' => 'count',
        ]));

        add_action('created_workspaces', $update);
        add_action('delete_workspaces', $update);
    }

    /**
     * Creates a total notes count
     * So it is possible to provide
     * that number with a single query
     * from the API.
     *
     * @return void
     */
    protected function countNotes(): void {
        add_action('save_post_notes', function(string $postId, \WP_Post $post, bool $update) {
            if ($post->post_type !== 'notes') {
                return;
            }
        
            $totalNotes = (int) get_option('total_notes', 0);
            if (!$update) {
                update_option('total_notes', $totalNotes + 1);
            }
        }, 10, 3);

        add_action('before_delete_post', function(string $postId){
            $postType = get_post_type($postId);
            if ($postType !== 'notes') {
                return;
            }
        
            $totalNotes = (int) get_option('total_notes', 0);
            update_option('total_notes', max(0, $totalNotes - 1));
        }, 10, 1);
    }

    /**
     * Initiates the notes by workspace options
     * so it can be consumed by the indexNotesByWorkspace method
     *
     * @return void
     */
    protected function initiateWorkspaceIndexedOptions(): void {
        add_action('created_workspaces', fn (string $termId) => add_option('notes_in_workspace_' . $termId, []));
        add_action('delete_workspaces', fn (string $termId) => delete_option('notes_in_workspace_' . $termId));
    }

    /**
     * Creates a hash table like structure, where
     * a workspace, found by its id as the last 
     * element in notes_in_workspace_{any-id}, 
     * gathers all the associated notes.
     * 
     * This will prevent a costy select/join of tables
     * 
     * @return void
     */
    protected function indexNotesByWorkspace(): void {
        // add to the indexed list when saving a post
        add_action('save_post', function(string $postId, \WP_Post $note){
            if ($note->post_type !== 'notes') return;

            $noteWorkspacesArray = getNoteWorkspaces($note);

            foreach ($noteWorkspacesArray as $workspaceId) {
                $option = 'notes_in_workspace_' . $workspaceId;
                $notesInWorkspace = get_option($option) ?? [];
                if (is_array($notesInWorkspace) && !in_array($postId, $notesInWorkspace)) {
                    update_option($option, [...$notesInWorkspace, $postId]);
                }
            }
        }, 10, 3);

        add_action('before_delete_post', function(string $postId){
            $postType = get_post_type($postId);
            if ($postType !== 'notes') return;

            $noteWorkspacesArray = getNoteWorkspaces(get_post($postId));

            foreach ($noteWorkspacesArray as $workspaceId) {
                $option = 'notes_in_workspace_' . $workspaceId;
                $notesInWorkspace = is_array(get_option($option)) ? get_option($option) : [];
                if(is_array($notesInWorkspace)) {
                    $noteToRemove = array_search($postId ,$notesInWorkspace);
                    if (is_integer($noteToRemove)) {
                        update_option($option, array_diff($notesInWorkspace, [$postId]));
                    }
                }
            }
        }, 10, 1);
    }
}
