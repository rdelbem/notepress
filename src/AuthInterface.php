<?php

namespace Olmec\OlmecNotepress;
use Olmec\OlmecNotepress\Types\AuthHeader;

if(!defined('ABSPATH')){
    exit;
}

use \WP_REST_Request;
use \WP_User;

interface AuthInterface {
    public function validateRefreshToken(WP_User $user): bool;
    public function createSession(WP_User $user): void;
    public function generateJwtAtLogin(): void;
    public function validateJwt(WP_REST_Request $request): bool;
    public function removeJwt(): void;
}