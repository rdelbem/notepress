<?php

namespace Olmec\OlmecNotepress;

if (!defined('ABSPATH')) {
    exit;
}

use WP_REST_Request;
use WP_User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Olmec\OlmecNotepress\AuthInterface;
use Olmec\OlmecNotepress\Util\LoadEnvVars;
use Olmec\OlmecNotepress\Types\RefreshToken;
use Olmec\OlmecNotepress\Types\JWT as NotepressJWT;
use Olmec\OlmecNotepress\Util\ErrorHandler;

final class Auth implements AuthInterface
{
    use LoadEnvVars;
    use ErrorHandler;

    /**
     * The secret hash key used for JWT encoding and encryption.
     *
     * @var string
     */
    private string $hash;

    /**
     * The JWT payload object.
     *
     * @var NotepressJWT
     */
    private NotepressJWT $jwt;

    /**
     * Whether or not the JWT is valid.
     * @var bool
     */
    private bool $isValidJwt = false;

    /**
     * Whether or not the API JWT is valid.
     * @var bool
     */
    private bool $apiJwtValidation = false;

    /**
     * Class constructor.
     * Initializes the environment variables and sets up JWT generation and removal hooks.
     */
    public function __construct()
    {
        $this->loadEnvVars();
        $this->hash = $_ENV['JWT_HASH_KEY'];
        $this->generateJwtAtLogin();
        $this->removeJwt();
    }

    // JWT-related methods

    /**
     * Creates a JWT for the given user.
     *
     * @param WP_User $user The WordPress user object.
     * @return NotepressJWT The JWT payload object.
     */
    private function createJWT(WP_User $user): NotepressJWT
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + (10 * HOUR_IN_SECONDS);
        $hashedIssuer = $this->encrypt(get_bloginfo('url'), $this->hash);

        $payload = new NotepressJWT(
            $issuedAt,
            $hashedIssuer,
            $expirationTime,
            $user->ID
        );

        return $payload;
    }

    /**
     * Sets the JWT payload object.
     *
     * @param NotepressJWT $jwt The JWT payload object.
     * @return void
     */
    private function setJwt(NotepressJWT $jwt): void
    {
        $this->jwt = $jwt;
    }

    /**
     * Retrieves the JWT payload object.
     *
     * @return NotepressJWT The JWT payload object.
     */
    public function getJwt(): NotepressJWT
    {
        return $this->jwt;
    }

    /**
     * Encodes the JWT payload into a JWT string.
     *
     * @return string The encoded JWT string.
     */
    public function getEncodedJWT(): string
    {
        return JWT::encode($this->getJwt()->toArray(), $this->hash, 'HS256');
    }

    // Refresh token methods

    /**
     * Generates a new refresh token.
     *
     * @return string The refresh token string.
     */
    private function generateRefreshToken(): string
    {
        return $this->hash;
    }

    /**
     * Creates a refresh token payload.
     *
     * @return RefreshToken The refresh token payload object.
     */
    private function createRefreshToken(): RefreshToken
    {
        $exp = time() + (10 * HOUR_IN_SECONDS) - (15 * MINUTE_IN_SECONDS);

        $payload = new RefreshToken(
            get_option('notepress_owner_id'),
            $exp
        );

        return $payload;
    }

    /**
     * Saves a new refresh token to the database.
     *
     * @return void
     */
    private function saveNewRefreshToken(): void
    {
        update_option('refresh_token', $this->createRefreshToken()->toArray());
    }

    /**
     * Retrieves the refresh token key.
     *
     * @return string|null The refresh token key or null if not found.
     */
    private function getRefreshTokenKey(): ?string
    {
        $refreshToken = $this->getRefreshToken();
        return $refreshToken === null ? null : $refreshToken->refresh_token;
    }

    /**
     * Retrieves the refresh token object.
     *
     * @return RefreshToken|null The refresh token object or null if not found.
     */
    private function getRefreshToken(): ?RefreshToken
    {
        $tokenOption = get_option('refresh_token', null);
        if (!$tokenOption) {
            return null;
        }

        $ourToken = new RefreshToken($tokenOption['refresh_token'], $tokenOption['exp']);
        return $ourToken;
    }

    /**
     * Validates the refresh token for the given user.
     *
     * @param WP_User $user The WordPress user object.
     * @return bool True if the refresh token is valid, false otherwise.
     */
    public function validateRefreshToken(WP_User $user): bool
    {
        $ourToken = $this->getRefreshToken();
        if (!$ourToken) {
            return false;
        }

        if ($ourToken->exp < time() && $user->ID !== explode('#', get_option('notepress_owner_id'))[1]) {
            return false;
        }

        return true;
    }

    // Encryption methods

    /**
     * Encrypts a given value using the specified key.
     *
     * @param string $value The value to encrypt.
     * @param string $key The encryption key.
     * @return string The encrypted value.
     */
    private function encrypt(string $value, string $key): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);

        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypts a given value using the specified key.
     *
     * @param string $value The value to decrypt.
     * @param string $key The decryption key.
     * @return string The decrypted value.
     */
    private function decrypt(string $value, string $key): string
    {
        list($encryptedData, $iv) = explode('::', base64_decode($value), 2);

        return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    }

    // Session methods

    /**
     * Creates a new session for the given user.
     *
     * @param WP_User $user The WordPress user object.
     * @return void
     */
    public function createSession(WP_User $user): void
    {
        if (!$this->validateRefreshToken($user)) {
            $this->saveNewRefreshToken();
        }

        $payload = $this->createJWT($user);
        $jwt = JWT::encode($payload->toArray(), $this->hash, 'HS256');

        if ($this->apiJwtValidation) {
            $this->saveNewRefreshToken();

            $decodedJWT = JWT::decode($jwt, new Key($this->hash, 'HS256'));
            $ourJWT = new NotepressJWT($decodedJWT->iat, $decodedJWT->iss, $decodedJWT->exp, $decodedJWT->uid);

            $this->setJwt($ourJWT);
        } else {
            setcookie('np_jwt_exp', $payload->exp, $payload->exp, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
            setcookie('jwt', $jwt, $payload->exp, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
        }
    }

    /**
     * Generates JWT at user login.
     *
     * @return void
     */
    public function generateJwtAtLogin(): void
    {
        add_action('wp_login', function ($_, WP_User $user) {
            $this->createSession($user);
        }, 10, 2);
    }

    /**
     * Removes the JWT at user logout.
     *
     * @return void
     */
    public function removeJwt(): void
    {
        add_action('wp_logout', function () {
            setcookie('np_jwt_exp', '', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl());
            setcookie('jwt', '', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        });
    }

    // Validation methods

    /**
     * Validates the JWT from the request.
     *
     * @param WP_REST_Request $request The REST request object.
     * @return bool True if the JWT is valid, false otherwise.
     */
    public function validateJwt(WP_REST_Request $request): bool
    {
        if (!isset($request->get_headers()['authorization'])) {
            return false;
        }

        $jwt = $request->get_headers()['authorization'][0];

        try {
            $decodedJWT = JWT::decode($jwt, new Key($this->hash, 'HS256'));
            $ourJWT = new NotepressJWT(
                $decodedJWT->iat,
                $decodedJWT->iss,
                $decodedJWT->exp,
                $decodedJWT->uid
            );
            $user = get_user_by('ID', $decodedJWT->uid);

            if ($ourJWT->exp < time()) {
                return false;
            }

            // This validation implies the refresh token stored in the db is about to expire and we can issue a new session.
            if ($this->validateRefreshToken($user)) {
                $this->createSession($user);
            }

            $isValid = $this->decrypt($ourJWT->iss, $this->hash) === get_bloginfo('url');
            $this->isValidJwt = $isValid;
            
            return $isValid;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validates the JWT through an api request.
     * @param \WP_REST_Request $request
     * @return \Olmec\OlmecNotepress\Auth
     */
    public function apiValidateJwt(WP_REST_Request $request): self {
        $this->apiJwtValidation = true;

        $this->validateJwt($request);
        return $this;
    }

    /**
     * Gets the JWT from the request.
     * @return never
     */
    public function getNewTokenOrNot() {
        if($this->isValidJwt){
            wp_send_json($this->getJwt(), 200);
            exit;
        }

        wp_send_json(['error' => 'Unauthorized'], 401);
        exit;
    }
}
