<?php 

namespace Olmec\OlmecNotepress\Types;

final class CreateNoteInput {
    public string $title;
    public string $workspaces;

    public function __construct(string $title, array $workspaces){
        $this->title = $title;
        $this->workspaces = $workspaces;
    }
}