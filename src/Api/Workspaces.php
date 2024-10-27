<?php

namespace Olmec\OlmecNotepress\Api;

use Olmec\OlmecNotepress\Types\Workspace;
use function Olmec\OlmecNotepress\Util\getWorkspacesCount;

if (!defined('ABSPATH')) {
    exit;
}

final class Workspaces {

    private function mapTermsToWorkspaces($terms) {
        $workspaces = [];
        foreach ($terms as $term) {
            $workspace = $this->mapTermToWorkspace($term);
            if ($workspace) {
                $workspaces[] = $workspace;
            }
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

    public function getAll($pageNumber = 1, $termsPerPage = 10) {
        $offset = ($pageNumber - 1) * $termsPerPage;

        $terms = get_terms([
            'taxonomy'   => OLMEC_NOTEPRESS_TAXONOMY_NAME,
            'hide_empty' => false,
            'number'     => $termsPerPage,
            'offset'     => $offset,
        ]);

        if (is_wp_error($terms)) {
            wp_send_json_error('Error retrieving workspaces: ' . $terms->get_error_message());
            exit;
        }

        $response = [
            'total'      => getWorkspacesCount(),
            'pageNumber' => $pageNumber,
            'workspaces' => $this->mapTermsToWorkspaces($terms),
        ];

        wp_send_json($response);
        exit;
    }

    public function getById($termId) {
        $term = get_term_by('id', $termId, OLMEC_NOTEPRESS_TAXONOMY_NAME);

        if (!$term || is_wp_error($term)) {
            wp_send_json_error('Workspace not found');
            exit;
        }

        $workspace = $this->mapTermToWorkspace($term);

        wp_send_json($workspace);
        exit;
    }

    public function create(\WP_REST_Request $request) {
        $data = json_decode($request->get_body(), true);
        $termName = isset($data['name']) ? $data['name'] : '';

        if (empty($termName)) {
            wp_send_json_error('Workspace name is required');
            exit;
        }

        $createdTerm = wp_insert_term($termName, OLMEC_NOTEPRESS_TAXONOMY_NAME);

        if (is_wp_error($createdTerm)) {
            wp_send_json_error('Error creating workspace: ' . $createdTerm->get_error_message());
            exit;
        }

        $term = get_term($createdTerm['term_id'], OLMEC_NOTEPRESS_TAXONOMY_NAME);
        $workspace = $this->mapTermToWorkspace($term);

        wp_send_json($workspace);
        exit;
    }

    public function update(\WP_REST_Request $request) {
        $termId = (int) $request->get_param('id');
        $termData = json_decode($request->get_body(), true);

        if (empty($termData) || !isset($termData['name'])) {
            wp_send_json_error('Workspace data is invalid');
            exit;
        }

        $updatedTerm = wp_update_term($termId, OLMEC_NOTEPRESS_TAXONOMY_NAME, $termData);

        if (is_wp_error($updatedTerm)) {
            wp_send_json_error('Error updating workspace: ' . $updatedTerm->get_error_message());
            exit;
        }

        $term = get_term($termId, OLMEC_NOTEPRESS_TAXONOMY_NAME);
        $workspace = $this->mapTermToWorkspace($term);

        wp_send_json($workspace);
        exit;
    }

    public function delete($termId) {
        $deleted = wp_delete_term($termId, OLMEC_NOTEPRESS_TAXONOMY_NAME);

        if (is_wp_error($deleted)) {
            wp_send_json_error('Error deleting workspace: ' . $deleted->get_error_message());
            exit;
        }

        wp_send_json_success('Workspace deleted successfully');
        exit;
    }

    public function search($keyword, $pageNumber = 1, $termsPerPage = 10) {
        $offset = ($pageNumber - 1) * $termsPerPage;

        $args = [
            'taxonomy'   => OLMEC_NOTEPRESS_TAXONOMY_NAME,
            'hide_empty' => false,
            'name__like' => $keyword,
            'number'     => $termsPerPage,
            'offset'     => $offset,
        ];

        $terms = get_terms($args);

        if (is_wp_error($terms)) {
            wp_send_json_error('Error retrieving workspaces: ' . $terms->get_error_message());
            exit;
        }

        $response = [
            'total'      => count($terms),
            'pageNumber' => $pageNumber,
            'workspaces' => $this->mapTermsToWorkspaces($terms),
        ];

        wp_send_json($response);
        exit;
    }
}
