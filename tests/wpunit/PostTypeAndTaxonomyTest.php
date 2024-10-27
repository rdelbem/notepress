<?php

use Codeception\TestCase\WPTestCase;
use Olmec\OlmecNotepress\PostTypeAndTaxonomy;

class PostTypeAndTaxonomyTest extends WPTestCase
{
    public function testPostTypeAndTaxonomyRegistration()
    {
        $postTypeArgs = [
            'labels' => [
                'name' => 'Notes',
                'singular_name' => 'Note'
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
            'taxonomies'  => ['workspaces'],
        ];
        
        $taxonomyArgs = [
            'labels' => [
                'name' => 'Workspaces',
                'singular_name' => 'Workspace'
            ],
            'public' => true,
            'hierarchical' => true,
        ];

        new PostTypeAndTaxonomy($postTypeArgs, $taxonomyArgs);

        do_action('init');

        $this->assertTrue(post_type_exists('notes'), 'Post type "notes" should be registered.');

        $postTypeObject = get_post_type_object('notes');
        $this->assertNotNull($postTypeObject, 'Post type object for "notes" should not be null.');
        $this->assertEquals('Notes', $postTypeObject->label, 'Post type label should be "Notes".');
        $this->assertTrue($postTypeObject->public, 'Post type "notes" should be public.');
        $this->assertTrue(post_type_supports('notes', 'title'), 'Post type "notes" should support "title".');
        $this->assertTrue(post_type_supports('notes', 'editor'), 'Post type "notes" should support "editor".');
        $this->assertTrue(post_type_supports('notes', 'author'), 'Post type "notes" should support "author".');
        $this->assertTrue(taxonomy_exists('workspaces'), 'Taxonomy "workspaces" should be registered.');

        $objectTaxonomies = get_object_taxonomies('notes', 'names');
        $this->assertContains('workspaces', $objectTaxonomies, 'Taxonomy "workspaces" should be associated with post type "notes".');

        $taxonomyObject = get_taxonomy('workspaces');
        $this->assertNotNull($taxonomyObject, 'Taxonomy object for "workspaces" should not be null.');
        $this->assertEquals('Workspaces', $taxonomyObject->label, 'Taxonomy label should be "Workspaces".');
        $this->assertTrue($taxonomyObject->hierarchical, 'Taxonomy "workspaces" should be hierarchical.');
        $this->assertTrue(in_array('notes', $taxonomyObject->object_type), 'Taxonomy "workspaces" should be associated with post type "notes".');
    }
}
