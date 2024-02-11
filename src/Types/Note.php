<?php

namespace Olmec\OlmecNotepress\Types;

use Olmec\OlmecNotepress\Types\Author;

final class Note
{
    public string $title;
    public int $id;
    public Author $author;
    public string $content;
    public string $created_at;
    public string $updated_at;

    public function __construct(string $title, int $id, Author $author, string $content, string $created_at, string $updated_at)
    {
        $this->title = $title;
        $this->id = $id;
        $this->author = $author;
        $this->content = $content;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }
}