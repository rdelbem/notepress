<?php

namespace Olmec\OlmecNotepress\Types;

final class RefreshToken{
    public string $refresh_token;
    public string $exp;
    public function __construct(string $refresh_token, string $exp) {
        $this->exp = $exp;
        $this->refresh_token = $refresh_token;
    }

    public function toArray(): array {
        return [
            'refresh_token' => $this->refresh_token,
            'exp' => $this->exp
        ];
    }
}