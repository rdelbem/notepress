<?php 

namespace Olmec\OlmecNotepress\Types;

class Author {
    public string $display_name;
    public int $id;

    public function __construct(string $display_name, int $id) {
        $this->display_name = $display_name;
        $this->id = $id;
    }
}