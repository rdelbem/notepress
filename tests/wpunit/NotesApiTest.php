<?php

use Codeception\TestCase\WPTestCase;
use Olmec\OlmecNotepress\Api\Notes;

class NotesApiTest extends WPTestCase
{
    private $notes;

    public function setUp(): void
    {
        parent::setUp();

        $this->factory->post->create_many(10, [
            'post_type' => 'notes',
            'post_status' => 'publish',
        ]);

        if (!function_exists('Olmec\OlmecNotepress\Util\excerptFromText')) {
            require_once __DIR__ . '/../../src/Util/functions.php';
        }

        $this->notes = $this->getMockBuilder(Notes::class)
                            ->onlyMethods(['sendJsonResponse'])
                            ->getMock();
    }

    public function testGetAllNotes()
    {
        // $this->notes->expects($this->once())
        //             ->method('sendJsonResponse')
        //             ->with($this->callback(function($response) {
        //                 // Assert that the response structure is correct
        //                 $this->assertArrayHasKey('total', $response);
        //                 // $this->assertCount(9, $response['notes']);
        //                 return true;
        //             }), 200);

        // $this->notes->getAll(1);
    }

    public function tearDown(): void
    {
        unset($this->notes);
        parent::tearDown();
    }
}

