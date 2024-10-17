<?php 

namespace Olmec\OlmecNotepress\Types;

/**
 * Used to convert a taxonomy to a
 * Notepress Workspace type, relates
 * as a parent/category to a note
 */
final class Workspace {
    public int $id;
    public string $name;

    public function __construct(int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }
}