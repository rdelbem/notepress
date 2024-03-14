<?php

namespace Olmec\OlmecNotepress\Api;

use Olmec\OlmecNotepress\Types\Workspace;
use function Olmec\OlmecNotepress\Util\getWorkspacesCount;

if(!defined('ABSPATH')){
    exit;
}

final class Workspaces {

    private function mapTermsToWorkspaces($terms) {
        $workspaces = [];
        foreach ($terms as $term) {
            $workspaces[] = $this->mapTermToWorkspace($term);
        }
        return $workspaces;
    }

    private function mapTermToWorkspace($term) {
        if ($term instanceof \WP_Term) {
            return new Workspace(
                (int) $term->term_id,
                $term->name
            );
        }
    
        return null;
    }

    function getAll($pageNumber = 1, $termsPerPage = 10) {
        $offset = ($pageNumber - 1) * $termsPerPage;
        $cacheKey = 'workspaces_get_all_' . $pageNumber;
        $cachedWorkspaces = get_transient($cacheKey);

        if ((bool) $cachedWorkspaces) {
            wp_send_json($cachedWorkspaces);
            exit;
        }

        $terms = get_terms([
            'taxonomy' => OLMEC_NOTEPRESS_TAXONOMY_NAME,
            'hide_empty' => false,
            'number' => $termsPerPage,
            'offset' => $offset,
        ]);

        if (is_wp_error($terms)) {
            wp_send_json('Error retrieving workspaces: ' . $terms->get_error_message());
            exit;
        }

        $createResponse = [
            'total' => getWorkspacesCount(),
            'pageNumber' => $pageNumber,
            'workspaces' => $this->mapTermsToWorkspaces($terms)
        ];

        set_transient($cacheKey, $createResponse, 60);

        wp_send_json($createResponse);
        exit;
    }

    function getById($termId) {
        $cacheKey = 'workspace_' . $termId;
        $cachedWorkspace = get_transient($cacheKey);

        if ($cachedWorkspace) {
            wp_send_json($this->mapTermToWorkspace($cachedWorkspace));
            exit;
        }

        $term = get_term_by('id', $termId, OLMEC_NOTEPRESS_TAXONOMY_NAME);

        if (!$term) {
            wp_send_json('Workspace not found');
            exit;
        }

        set_transient($cacheKey, $term, MINUTE_IN_SECONDS);

        wp_send_json($this->mapTermToWorkspace($term));
        exit;
    }

    function create(\WP_REST_Request $request) {
        $termName = json_decode($request->get_body())->name;
        $createdTerm = wp_insert_term($termName, OLMEC_NOTEPRESS_TAXONOMY_NAME);

        if (is_wp_error($createdTerm)) {
            wp_send_json($createdTerm->get_error_message());
            exit;
        }

        wp_send_json($this->mapTermToWorkspace($createdTerm));
        exit;
    }

    function update(\WP_REST_Request $request) {
        $termId = (int) $request->get_param('id');
        $termData = json_decode($request->get_body(), true);
        $updatedTerm = wp_update_term($termId, OLMEC_NOTEPRESS_TAXONOMY_NAME, $termData);

        if (is_wp_error($updatedTerm)) {
            wp_send_json($updatedTerm->get_error_message());
            exit;
        }

        wp_send_json($updatedTerm);
        exit;
    }

    function delete($termId) {
        $deletedTerm = wp_delete_term($termId, OLMEC_NOTEPRESS_TAXONOMY_NAME);

        if (is_wp_error($deletedTerm)) {
            wp_send_json($deletedTerm->get_error_message());
            exit;
        }

        wp_send_json('Workspace deleted successfully');
        exit;
    }

    function search($keyword, $pageNumber = 1, $termsPerPage = 10) {
        $offset = ($pageNumber - 1) * $termsPerPage;
        $cacheKey = 'workspaces_search_' . md5(trim($keyword));
        $cachedWorkspaces = get_transient($cacheKey);
    
        if ($cachedWorkspaces) {
            wp_send_json($cachedWorkspaces);
            exit;
        }
    
        $args = [
            'taxonomy' => OLMEC_NOTEPRESS_TAXONOMY_NAME,
            'hide_empty' => false,
            'name__like' => $keyword,
            'number' => $termsPerPage,
            'offset' => $offset,
        ];
    
        $terms = get_terms($args);
    
        if (is_wp_error($terms)) {
            wp_send_json('Error retrieving workspaces');
            exit;
        }
    
        set_transient($cacheKey, $terms, MINUTE_IN_SECONDS/2);
    
        wp_send_json($terms);
        exit;
    }
}