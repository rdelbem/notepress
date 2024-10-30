<?php

namespace Olmec\OlmecNotepress\Api;

use Olmec\OlmecNotepress\Types\Note;
use Olmec\OlmecNotepress\Types\Author;
use function Olmec\OlmecNotepress\Util\excerptFromText;
use function Olmec\OlmecNotepress\Util\getNotesCount;
use function Olmec\OlmecNotepress\Util\setNotesCount;

if (!defined('ABSPATH')) {
    exit;
}

class Notes
{
    const NOTES_PER_PAGE = 9;
    const ALL_NOTES_CACHE_KEY = 'notes_get_all';

    public function sendJsonResponse($response, $statusCode = 200)
    {
        wp_send_json($response, $statusCode);
    }

    public function getAll(int $pageNumber = 1)
    {
        $args = [
            'post_type' => 'notes',
            'post_status' => 'publish',
            'paged' => $pageNumber,
            'posts_per_page' => self::NOTES_PER_PAGE,
        ];

        $notesQuery = new \WP_Query($args);
        $notesArray = [];

        if ($notesQuery->have_posts()) {
            while ($notesQuery->have_posts()) {
                global $post;
                $notesQuery->the_post();

                $authorAvatar = get_avatar_url(get_the_author_meta('ID'), ['size' => 450]);
                $author = new Author(
                    (int) get_the_author_meta('id'),
                    get_the_author_meta('display_name'),
                    $authorAvatar ? $authorAvatar : null
                );

                $workspacesAsWpTerms = get_the_terms($post, 'workspaces');
                $workspacesArray = [];
                if (!is_wp_error($workspacesAsWpTerms) && is_array($workspacesAsWpTerms)) {
                    foreach ($workspacesAsWpTerms as $workspace) {
                        $workspacesArray[] = $workspace->name . ':' . $workspace->term_id;
                    }
                }

                $notesArray[] = new Note(
                    get_the_title(),
                    get_the_ID(),
                    $author,
                    excerptFromText(get_the_content()), // Here it is not necessary to send the full text
                    is_array($workspacesArray) ? implode(',', $workspacesArray) : '',
                    get_the_date('c'),
                    get_the_modified_date('c')
                );
            }

            $createResponse = [
                'total' => getNotesCount(),
                'pageNumber' => $pageNumber,
                'notes' => $notesArray
            ];

            $this->sendJsonResponse($createResponse);
            wp_reset_postdata();
            exit;
        }

        $this->sendJsonResponse('No posts found');
        exit;
    }

    public function getById(int $noteId)
    {
        // get post is slightly more performant than WP_Query
        $note = get_post($noteId);

        $workspacesAsWpTerms = get_the_terms($note, 'workspaces');
        $workspacesArray = [];
        if (!is_wp_error($workspacesAsWpTerms) && is_array($workspacesAsWpTerms)) {
            foreach ($workspacesAsWpTerms as $workspace) {
                $workspacesArray[] = $workspace->name . ':' . $workspace->term_id;
            }
        }
        if ($note && $note->post_type == 'notes') {
            $this->sendJsonResponse(new Note(
                $note->post_title,
                (int) $note->ID,
                new Author(
                    (int) get_the_author_meta('display_name', $note->post_author),
                    $note->post_author
                ),
                $note->post_content,
                is_array($workspacesArray) ? implode(',', $workspacesArray) : '',
                date('c', strtotime($note->post_date)), // to ISO 8601
                date('c', strtotime($note->post_modified)) // to ISO 8601
            ));
            exit;
        }
        $this->sendJsonResponse('Note ' . $noteId . ' not found');
        exit;
    }

    public function create(\WP_REST_Request $note)
    {
        $noteWorkspaces = null;
        $noteTitle = $note->get_param('title');
        $noteWorkspacesArrayOfWorkspaceIds = [];

        if ($note->get_param('workspaces') && strpos($note->get_param('workspaces'), ',') !== false) {
            $noteWorkspaces = explode(',', $note->get_param('workspaces'));
        } else {
            $category = get_term_by('name', $note->get_param('workspaces'), OLMEC_NOTEPRESS_TAXONOMY_NAME);
            if ($category instanceof \WP_Term) {
                $noteWorkspacesArrayOfWorkspaceIds[] = (int) $category->term_id;
            } else {
                $termArray = wp_insert_term($note->get_param('workspaces'), OLMEC_NOTEPRESS_TAXONOMY_NAME);
                if (!is_wp_error($termArray)) {
                    $noteWorkspacesArrayOfWorkspaceIds[] = (int) $termArray['term_id'];
                }
            }
        }

        if (is_array($noteWorkspaces)) {
            foreach ($noteWorkspaces as $workspaceName) {
                $category = get_term_by('name', $workspaceName, OLMEC_NOTEPRESS_TAXONOMY_NAME);
                if ($category instanceof \WP_Term) {
                    $noteWorkspacesArrayOfWorkspaceIds[] = (int) $category->term_id;
                } else {
                    $termArray = wp_insert_term($workspaceName, OLMEC_NOTEPRESS_TAXONOMY_NAME);
                    if (!is_wp_error($termArray)) {
                        $noteWorkspacesArrayOfWorkspaceIds[] = (int) $termArray['term_id'];
                    }
                }
            }
        }

        $args = [
            'post_title' => $noteTitle,
            'post_status' => 'publish',
            'post_type' => 'notes',
            'tax_input' => [
                OLMEC_NOTEPRESS_TAXONOMY_NAME => $noteWorkspacesArrayOfWorkspaceIds
            ]
        ];

        $response = wp_insert_post($args, true);

        if (is_wp_error($response)) {
            $errorMessage = 'Unknown error while creating a note';
            $response !== null ? $this->sendJsonResponse($response->get_error_message() ?? $errorMessage) : $this->sendJsonResponse($errorMessage);
            exit;
        }

        if (count($noteWorkspacesArrayOfWorkspaceIds) > 0) {
            foreach (explode(',', $note->get_param('workspaces')) as $workspace) {
                setNotesCount($workspace, true);
            }
        } elseif (count($noteWorkspacesArrayOfWorkspaceIds) === 1) {
            setNotesCount($note->get_param('workspaces'), true);
        }

        $newNoteInfos = get_post($response);
        $workspacesAsWpTerms = get_the_terms($newNoteInfos, 'workspaces');
        $workspacesArray = [];
        if (!is_wp_error($workspacesAsWpTerms) && is_array($workspacesAsWpTerms)) {
            foreach ($workspacesAsWpTerms as $workspace) {
                $workspacesArray[] = $workspace->name . ':' . $workspace->term_id;
            }
        }

        $authorAvatar = get_avatar_url(get_the_author_meta('ID', $newNoteInfos->post_author), ['size' => 450]);
        $author = new Author(
            (int) get_the_author_meta('id', $newNoteInfos->post_author),
            get_the_author_meta('display_name', $newNoteInfos->post_author),
            $authorAvatar ? $authorAvatar : null
        );
        $newNote = new Note(
            get_the_title($newNoteInfos),
            $newNoteInfos->ID,
            $author,
            '',
            is_array($workspacesArray) ? implode(',', $workspacesArray) : '',
            get_the_date('c', $newNoteInfos),
            get_the_modified_date('c', $newNoteInfos)
        );

        $this->sendJsonResponse($newNote);
        exit;
    }

    public function update(\WP_REST_Request $note)
    {
        $noteId = $note->get_param('id');
        $noteBody = json_decode($note->get_body(), true);

        if (!$noteId || !$noteBody) {
            $this->sendJsonResponse('Invalid request', 400);
            exit;
        }

        $response = wp_update_post([
            'ID'           => $noteId,
            'post_content' => $noteBody['post_content'],
        ], true);

        if (is_wp_error($response)) {
            $this->sendJsonResponse($response->get_error_message(), 400);
            exit;
        }

        return (bool) $response;
    }

    public function delete(int $noteId)
    {
        $post = get_post($noteId);
        $response = null;
        if ($post) {
            $response = wp_delete_post($noteId, true);
        }

        if (!is_wp_error($response)) {
            $this->sendJsonResponse('Note ' . $response->post_name . ' deleted.');
            exit;
        }

        $this->sendJsonResponse('It was not possible to delete note ' . $noteId);
        exit;
    }

    public function search(string $keyWord, int $pageNumber = 1)
    {
        $args = [
            'post_type' => 'notes',
            'post_status' => 'publish',
            's' => $keyWord,
            'posts_per_page' => self::NOTES_PER_PAGE,
            'paged' => $pageNumber,
        ];

        $notesQuery = new \WP_Query($args);
        $notesArray = [];

        if (!$notesQuery->have_posts()) {
            $this->sendJsonResponse('No notes found using: ' . $keyWord);
            wp_reset_postdata();
            exit;
        }

        while ($notesQuery->have_posts()) {
            global $post;
            $notesQuery->the_post();
            $authorAvatar = get_avatar_url(get_the_author_meta('ID'), ['size' => 450]);
            $author = new Author(
                (int) get_the_author_meta('id'),
                get_the_author_meta('display_name'),
                $authorAvatar ? $authorAvatar : null
            );

            $workspacesAsWpTerms = get_the_terms($post, 'workspaces');
            $workspacesArray = [];
            if (!is_wp_error($workspacesAsWpTerms) && is_array($workspacesAsWpTerms)) {
                foreach ($workspacesAsWpTerms as $workspace) {
                    $workspacesArray[] = $workspace->name . ':' . $workspace->term_id;
                }
            }

            $notesArray[] = new Note(
                get_the_title(),
                get_the_ID(),
                $author,
                get_the_content(),
                is_array($workspacesArray) ? implode(',', $workspacesArray) : '',
                get_the_date('c'),
                get_the_modified_date('c')
            );
        }

        $this->sendJsonResponse($notesArray);
        wp_reset_postdata();
        exit;
    }

    public function getNotesByWorkspace(string $workspace, int $pageNumber = 1)
    {
        if (!$workspace) {
            $this->sendJsonResponse('No posts found');
            exit;
        }

        $cacheKey = "notes_by_workspace_{$workspace}_page_{$pageNumber}";
        $cachedNotes = get_transient($cacheKey);

        if ($cachedNotes !== false) {
            // cached response if available
            $this->sendJsonResponse($cachedNotes);
            exit;
        }

        $args = [
            'post_type' => 'notes',
            'posts_per_page' => self::NOTES_PER_PAGE,
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

        if ($notesQuery->have_posts()) {
            while ($notesQuery->have_posts()) {
                global $post;
                $notesQuery->the_post();

                $authorAvatar = get_avatar_url(get_the_author_meta('ID'), ['size' => 450]);
                $author = new Author(
                    (int) get_the_author_meta('ID'),
                    get_the_author_meta('display_name'),
                    $authorAvatar ? $authorAvatar : null
                );

                $workspacesAsWpTerms = get_the_terms($post, 'workspaces');
                $workspacesArray = [];
                if (!is_wp_error($workspacesAsWpTerms) && is_array($workspacesAsWpTerms)) {
                    foreach ($workspacesAsWpTerms as $workspace) {
                        $workspacesArray[] = $workspace->name . ':' . $workspace->term_id;
                    }
                }

                $notesArray[] = new Note(
                    get_the_title(),
                    get_the_ID(),
                    $author,
                    excerptFromText(get_the_content()), // Here we also do not need to send the full text
                    is_array($workspacesArray) ? implode(',', $workspacesArray) : '',
                    get_the_date('c'),
                    get_the_modified_date('c')
                );
            }

            $createResponse = [
                'total' => getNotesCount(is_string($workspace) ? $workspace : $workspace->slug),
                'pageNumber' => $pageNumber,
                'notes' => $notesArray
            ];

            // Cache the response for future use
            set_transient($cacheKey, $createResponse, HOUR_IN_SECONDS);

            $this->sendJsonResponse($createResponse);
            wp_reset_postdata();
            exit;
        }

        $this->sendJsonResponse('No posts found');
        exit;
    }
}
