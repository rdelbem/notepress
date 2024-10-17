<?php

namespace Olmec\OlmecNotepress\Types;

final class JWT {
    public string $iat;
    public string $iss;
    public string $exp;
    public string $uid;

    public function __construct(string $iat, string $iss, string $exp, string $uid) {
        $this->exp = $exp;
        $this->iat = $iat;
        $this->iss = $iss;
        $this->uid = $uid;
    }

    public function toArray(): array {
        return [
            'iat' => $this->iat,
            'iss' => $this->iss,
            'exp' => $this->exp,
            'uid' => $this->uid
        ];
    }
}