<?php

namespace Olmec\OlmecNotepress\Api;

use DateTime;
use DateTimeZone;
use \WP_REST_Request;
use Olmec\OlmecNotepress\Auth;
use Olmec\OlmecNotepress\Util\ErrorHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class HeartBeat
{
    use ErrorHandler;
    private int $notepressOwner;
    
    private int $lastLoggedInAt;

    /**
     * @var array{'exp': int, 'refresh_token': string} $refreshToken
     */
    private array $refreshToken;

    private Auth $auth;

    function __construct()
    {
        $this->notepressOwner = get_option('notepress_owner');
        $this->lastLoggedInAt = get_option("{$this->notepressOwner}_logged_at");
        $this->refreshToken = get_option('refresh_token');
        $this->auth = new Auth();
    }

    public function run(WP_REST_Request $request): void
    {
        $this->verifySession($request);
    }

    private function renewSession(): void
    {
        $user = wp_get_current_user();
        $isOwner = $user->ID === $this->notepressOwner;
        if ($isOwner && $user->ID > 0 && $this->lastLoggedInAt) {
            $this->auth->createSession($user);
        }
    }

    private function verifySession(WP_REST_Request $request): void
    {
        $authorizationHeader = json_decode($request->get_headers()['authorization'][0], true) ?? null;

        if ($authorizationHeader === null) {
            $this->setError('No authorization header provided');
        }

        /**
         * @var array{'exp': int, 'refresh_token': string} $refreshToken
         */
        $refreshToken = $authorizationHeader;

        if (!is_array($refreshToken) || !isset($refreshToken['exp']) || !isset($refreshToken['refresh_token'])) {
            $this->setError('Invalid authorization header shape');
        }

        $isValidJwt = $this->auth->validateJwt($request);

        $shouldRenew = time() >= $refreshToken['exp'] && $this->auth->validateRefreshToken($refreshToken);

        if ($shouldRenew && $isValidJwt) {
            $this->renewSession();
        }
    }
}
