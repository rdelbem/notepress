<?php

namespace Olmec\OlmecNotepress\Util;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add here functions that are not context specific and
 * would not perticularly fit to a class or a trait
 */

/**
 * Use this to trim texts to a certain
 * number of characters, but without leaving
 * word breaks
 *
 * @param string $string
 * @param integer $limit
 * @return $string
 */
function excerptFromText($string, $limit = 120)
{
    if (strlen($string) <= $limit)
        return $string;
    $cutMark = substr($string, 0, $limit);
    $lastSpace = strrpos($cutMark, ' ');
    return $lastSpace !== false ? substr($cutMark, 0, $lastSpace) : $cutMark;
}

/**
 * Returns the total amount of categories
 * that exist in workspaces
 *
 * @return integer
 */
function getWorkspacesCount(): int
{
    return (int) get_option('total_workspaces', 0);
}

/**
 * Gets the total amount of notes,
 * can recieve the category slug
 * 
 * @return integer
 */
function getNotesCount(string|bool $name = false): int
{
    if ($name)
        return (int) get_option($name, 0);
    return (int) get_option('total_notes', 0);
}

/**
 * Increments or dicrements the count of the specified notes group by 1
 *
 * @param string $slug
 * @param bool $increase
 * @return bool
 */
function setNotesCount(string $slug, bool $increase): bool
{
    $increase ? $notesCount = (int) getNotesCount($slug) + 1 : $notesCount = (int) getNotesCount($slug) - 1;
    return update_option($slug, $notesCount, true);
}

/**
 * Returns all the workspaces ids a given note is assigned to.
 *
 * @param \WP_Post $post
 * @return string[]
 */
function getNoteWorkspaces(\WP_Post $post): array
{
    $terms = get_the_terms($post->ID, 'workspaces');

    if (is_wp_error($terms) || !is_array($terms)) {
        return ['error' => 'Couldn`t find category terms or slugs for the provided note id.'];
    }

    $workspaces = [];
    foreach ($terms as $term) {
        $workspaces[] = $term->term_id;
    }

    return $workspaces;
}