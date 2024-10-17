<?php

use Codeception\TestCase\WPTestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Olmec\OlmecNotepress\Indexation;

class IndexationTest extends WPTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        register_taxonomy('workspaces', 'post', [
            'label' => 'Workspaces',
            'public' => true,
            'rewrite' => ['slug' => 'workspace'],
            'hierarchical' => true,
        ]);

        new Indexation();
    }

    public function tearDown(): void
    {
        Monkey\tearDown();

        parent::tearDown();
    }

    public function testCountWorkspaces()
    {
        $termId = $this->factory->term->create([
            'taxonomy' => 'workspaces'
        ]);

        $totalWorkspaces = get_option('total_workspaces');
        $this->assertEquals(1, $totalWorkspaces);

        wp_delete_term($termId, 'workspaces');
        $totalWorkspaces = get_option('total_workspaces');
        $this->assertEquals(0, $totalWorkspaces);
    }

    public function testCountNotes()
    {

        require_once __DIR__ . '/../../src/Util/functions.php';

        $postId = $this->factory->post->create([
            'post_type' => 'notes',
            'post_status' => 'publish'
        ]);

        $totalNotes = get_option('total_notes');
        $this->assertEquals(1, $totalNotes);

        wp_delete_post($postId);
        $totalNotes = get_option('total_notes');
        $this->assertEquals(0, $totalNotes);
    }

    public function testInitiateWorkspaceIndexedOptions()
    {
        $termId = $this->factory->term->create([
            'taxonomy' => 'workspaces'
        ]);

        $workspaceOption = get_option('notes_in_workspace_' . $termId);
        $this->assertIsArray($workspaceOption);

        wp_delete_term($termId, 'workspaces');
        $workspaceOption = get_option('notes_in_workspace_' . $termId);
        $this->assertFalse($workspaceOption); 
    }

    public function skipTestIndexNotesByWorkspace()
    {
        Functions\when('Olmec\OlmecNotepress\Util\getNoteWorkspaces')->justReturn([1]);
        $termId = $this->factory->term->create([
            'taxonomy' => 'workspaces'
        ]);

        $postId = $this->factory->post->create([
            'post_type' => 'notes',
            'post_status' => 'publish'
        ]);

        $notesInWorkspace = get_option('notes_in_workspace_' . $termId);
        $this->assertContains($postId, $notesInWorkspace);

        wp_delete_post($postId);
        $notesInWorkspace = get_option('notes_in_workspace_' . $termId);
        $this->assertNotContains($postId, $notesInWorkspace);
    }
}
