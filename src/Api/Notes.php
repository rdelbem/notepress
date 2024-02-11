<?php

namespace Olmec\OlmecNotepress\Api;

use Olmec\OlmecNotepress\Types\Note;
use Olmec\OlmecNotepress\Types\Author;
use function Olmec\OlmecNotepress\Util\excerptFromText;

if(!defined('ABSPATH')){
    exit;
}

final class Notes
{
    const POST_PER_PAGE = 30;
    const ALL_NOTES_CACHE_KEY = 'notes_get_all';

    function getAll(int $pageNumber = 1) {
        $cacheKey = self::ALL_NOTES_CACHE_KEY;
        $cachedNotes = get_transient($cacheKey);

        if((bool) $cachedNotes){
            wp_send_json($cachedNotes);
            exit;
        }
        
        $args = [
            'post_type' => 'notes',
            'post_status' => 'publish',
            'paged' => $pageNumber,
            'posts_per_page' => self::POST_PER_PAGE,
        ];
        
        $notesQuery = new \WP_Query($args);
        $notesArray = [];
        
        if ($notesQuery->have_posts()) {
            while ($notesQuery->have_posts()) {
                $notesQuery->the_post();

                $authorAvatar = get_avatar_url(get_the_author_meta('ID'), ['size' => 450]);
                $author = new Author(
                    (int) get_the_author_meta('id'),
                    get_the_author_meta('display_name'),
                    $authorAvatar ? $authorAvatar : null
                );

                $notesArray[] = new Note(
                    get_the_title(), 
                    get_the_ID(), 
                    $author,
                    excerptFromText(get_the_content()), // Here it is not necessary to send the full text
                    get_the_date('c'),
                    get_the_modified_date('c')
                );
            }

            set_transient($cacheKey, $notesArray, 60);

            wp_send_json($notesArray);
            wp_reset_postdata();
            exit;
        }
        
        wp_send_json('No posts found');
        exit;
    }

    function getById(int $noteId) {
        // get post is slightly more performant than WP_Query
        $note = get_post($noteId);

        if ($note && $note->post_type == 'notes') {
            wp_send_json([
                'title' => $note->post_title, 
                'id' => $note->ID, 
                'author' => [
                    'display_name' => get_the_author_meta('display_name', $note->post_author),
                    'id' => $note->post_author,
                    ] ,
                'content' => $note->post_content,
                'created_at' => date('c', strtotime($note->post_date)), // to ISO 8601
                'updated_at' => date('c', strtotime($note->post_modified)) // to ISO 8601
                ]);
            exit;
        }
        wp_send_json('Note ' . $noteId . ' not found');
        exit;
    }

    function create(\WP_REST_Request $note) {
        $noteWorkspaces = null;
        $noteTitle = $note->get_param('title');
        $noteWorkspacesArrayOfWorkspaceIds = [];

        if($note->get_param('workspaces') && strpos($note->get_param('workspaces'), ',') !== false){
            $noteWorkspaces = explode(',', $note->get_param('workspaces'));
        }

        // we might have only one category slug
        if($note->get_param('workspaces') && strpos($note->get_param('workspaces'), ',') === false){
            $category = get_term_by('slug', $noteWorkspaces, OLMEC_NOTEPRESS_TAXONOMY_NAME);
            if($category instanceof \WP_Term){
                $noteWorkspacesArrayOfWorkspaceIds[] = (int) $category->term_id;
            }
        }

        if(is_array($noteWorkspaces)){
            foreach ($noteWorkspaces as $workspaceName) {
                $category = get_term_by('slug', $workspaceName, OLMEC_NOTEPRESS_TAXONOMY_NAME);
                if($category instanceof \WP_Term){
                    $noteWorkspacesArrayOfWorkspaceIds[] = (int) $category->term_id;
                }
            }
        }

        $args = [
            'post_title'   => $noteTitle,
            'post_status'  => 'publish',
            'post_type'    => 'notes',
            'tax_input'    => [
                OLMEC_NOTEPRESS_TAXONOMY_NAME => $noteWorkspacesArrayOfWorkspaceIds
            ]
        ];

        $response = wp_insert_post($args, true);

        if(is_wp_error($response)){
            $errorMessage = 'Update failed, unknown error';
            $response !== null ? wp_send_json($response->get_error_message() ?? $errorMessage) : wp_send_json($errorMessage);
            exit;
        }

        // rebuild cache
        if(count($noteWorkspacesArrayOfWorkspaceIds) > 0){
            foreach (explode(',', $note->get_param('workspaces')) as $workspace) {
                delete_transient('notes_in_' . $workspace);
            }
        }elseif (count($noteWorkspacesArrayOfWorkspaceIds) === 1) {
            delete_transient('notes_in_' . $note->get_param('workspaces'));
        }
        delete_transient(self::ALL_NOTES_CACHE_KEY);

        wp_send_json(get_post($response));
        exit;
    }

    function update(\WP_REST_Request $note) {
        $noteId = $note->get_param('id');
        $noteWorkspaces = get_the_terms($noteId, 'workspaces');
        $noteBody = json_decode($note->get_body(), true);
    
        if(!$noteId || !$noteBody) {
            wp_send_json('Invalid request', 400);
            exit;
        }
    
        $response = wp_update_post([
            'ID'           => $noteId,
            'post_content' => $noteBody['post_content'],
        ], true);
    
        if(is_wp_error($response)) {
            wp_send_json($response->get_error_message(), 400);
            exit;
        }

        // delete cache, to force a rebuild on get
        if(is_array($noteWorkspaces)){
            foreach ($noteWorkspaces as $noteWorkspace) {
                delete_transient('notes_in_' . $noteWorkspace->slug);
            }
        }
        delete_transient(self::ALL_NOTES_CACHE_KEY);
        return (bool) $response;
    }

    function delete(int $noteId) {
        $post = get_post($noteId);
        $response = null;
        if ($post) {
            $noteWorkspaces = get_the_terms($noteId, 'workspaces');
            $response = wp_delete_post($noteId, true);
        }

        if($response instanceof \WP_Post) {
            delete_transient(self::ALL_NOTES_CACHE_KEY);
            if(is_array($noteWorkspaces)){
                foreach ($noteWorkspaces as $noteWorkspace) {
                    delete_transient('notes_in_' . $noteWorkspace->slug);
                }
            }
            wp_send_json('Note ' . $response->post_name . ' deleted');
            exit;
        }

        wp_send_json('It was not possible to delete note ' . $noteId);
        exit;
    }

    function search(string $keyWord, int $pageNumber = 1) {
        $cacheKey = 'notes_search_' . md5(trim($keyWord));
        $cachedNotes = get_transient($cacheKey);

        if((bool) $cachedNotes){
            wp_send_json($cachedNotes);
            exit;
        }

        $args = [
            'post_type' => 'notes',
            'post_status' => 'publish',
            's' => $keyWord,
            'posts_per_page' => self::POST_PER_PAGE,
            'paged' => $pageNumber,
        ];
    
        $notesQuery = new \WP_Query($args);
        $notesArray = [];
    
        if (!$notesQuery->have_posts()) {
            wp_send_json('No notes found using: ' . $keyWord);
            wp_reset_postdata();
            exit;
        }

        while ($notesQuery->have_posts()) {
            $notesQuery->the_post();
            $authorAvatar = get_avatar_url(get_the_author_meta('ID'), ['size' => 450]);
            $author = new Author(
                (int) get_the_author_meta('id'),
                get_the_author_meta('display_name'),
                $authorAvatar ? $authorAvatar : null
            );

            $notesArray[] = new Note(
                get_the_title(), 
                get_the_ID(), 
                $author,
                get_the_content(),
                get_the_date('c'),
                get_the_modified_date('c')
            );
        }

        set_transient($cacheKey, $notesArray, 120);

        wp_send_json($notesArray);
        wp_reset_postdata();
        exit;
    }

    public function getNotesByWorkspace(string $workspace, int $pageNumber = 1) {
        if(!$workspace) {
            wp_send_json('No posts found');
            exit;
        }

        $cacheKey = 'notes_in_' . $workspace;
        $cachedNotes = get_transient($cacheKey);

        if((bool) $cachedNotes){
            wp_send_json($cachedNotes);
            exit;
        }

        $args = [
            'post_type' => 'notes',
            'posts_per_page' => self::POST_PER_PAGE,
            'paged' => $pageNumber,
            'tax_query' => [
                [
                    'taxonomy' => 'workspaces',
                    'field' => 'slug',
                    'terms' => $workspace
                ]
            ]
        ];

        $notesQuery = new \WP_Query($args);
        $notesArray = [];

        if($notesQuery->have_posts()){
            while ($notesQuery->have_posts()) {
                $notesQuery->the_post();

                $authorAvatar = get_avatar_url(get_the_author_meta('ID'), ['size' => 450]);
                $author = new Author(
                    (int) get_the_author_meta('ID'),
                    get_the_author_meta('display_name'),
                    $authorAvatar ? $authorAvatar : null
                );

                $notesArray[] = new Note(
                    get_the_title(), 
                    get_the_ID(), 
                    $author,
                    excerptFromText(get_the_content()), // Here we also do not need to send the full text
                    get_the_date('c'),
                    get_the_modified_date('c')
                );
            }

            set_transient($cacheKey, $notesArray, 30);
            wp_send_json($notesArray);
            wp_reset_postdata();
            exit;
        }

        wp_send_json('No posts found');
        exit;
    }
}