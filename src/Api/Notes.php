<?php

namespace Olmec\OlmecNotepress\Api;

if(!defined('ABSPATH')){
    exit;
}

final class Notes
{
    function getAll() {
        $args = array(
            'post_type' => 'notes',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        
        $notesQuery = new \WP_Query($args);
        $notesArray = [];
        
        if ($notesQuery->have_posts()) {
            while ($notesQuery->have_posts()) {
                $notesQuery->the_post();
                $notesArray[] = [
                    'title' => get_the_title(), 
                    'id' => get_the_ID(), 
                    'author' => [
                        'display_name' => get_the_author_meta('display_name'),
                        'id' => get_the_author_meta('id'),
                        ] ,
                    'content' => get_the_content(),
                    'created_at' => get_the_date('c'),
                    'updated_at' => get_the_modified_date('c')
                    ];
            }

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
        $note['post_type'] = 'notes';
        $response = wp_insert_post(json_decode($note->get_body()), true);
        if($response instanceof \WP_Error || $response === null || $response === 0){
            $errorMessage = 'Update failed, unknown error';
            $response !== null ? wp_send_json($response->get_error_message() ?? $errorMessage) : wp_send_json($errorMessage);
            exit;
        }

        wp_send_json(get_post($note['id']));
        exit;
    }

    function update(\WP_REST_Request $note) {
        $post = get_post($note['id'], ARRAY_A);
        $response = null;
        if($post && $post->post_type === 'notes') {
            $response = wp_update_post([...$post, ...json_decode($note->get_body())], true);
        }
        if($response instanceof \WP_Error || $response === null || $response === 0){
            $errorMessage = 'Update failed, unknown error';
            $response !== null ? wp_send_json($response->get_error_message() ?? $errorMessage) : wp_send_json($errorMessage);
            exit;
        }

        wp_send_json(get_post($note['id']));
        exit;
    }

    function delete(int $noteId) {
        $post = get_post($noteId);
        $response = null;
        if ($post && $post->post_type === 'notes') $response = wp_delete_post($noteId, true);

        if($response instanceof \WP_Post) {
            wp_send_json('Note ' . $response->post_name . ' deleted');
            exit;
        }

        wp_send_json('It was not possible to delete note ' . $noteId);
        exit;
    }
}
