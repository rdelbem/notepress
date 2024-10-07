<?php 

namespace Olmec\OlmecNotepress\Types;

use Olmec\OlmecNotepress\Types\JWT;
use Olmec\OlmecNotepress\Types\RefreshToken;

final class AuthHeader{
    public JWT $jwt;
    public RefreshToken $refreshToken;

    public function __construct(JWT $jwt, RefreshToken $refreshToken) {
        $this->jwt = $jwt;
        $this->refreshToken = $refreshToken;
    }
}