<?php 

namespace Olmec\OlmecNotepress\Types;

final class Author {   
    public int $id;
    public string $display_name;
    public string | null $avatar;

    public function __construct(int $id, string $display_name, string | null $avatar = null) {
        $this->id = $id;
        $this->display_name = $display_name;
        $this->avatar = $avatar;
    }
}