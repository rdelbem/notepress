<?php 

namespace Olmec\OlmecNotepress\Types;

/**
 * Notepress User type definition
 * 
 * Avatar may be a 0 if not set, for that reason
 * we always evalualate or do not pass it to this
 * type constructor
 */
class User {
    public int $id;
    public string $display_name;
    public string | null $avatar;

    public function __construct(int $id, string $display_name, string | null $avatar = null) {
        $this->id = $id;
        $this->display_name = $display_name;
        $this->avatar = $avatar;
    }
}