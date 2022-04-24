<?php

use Firebase\JWT\JWT as FJWT;

/**
 * The URL class is designed to get, build, modify any element of a url, in order to retrieve it or redirect to it.
 */
class Url
{
    private ?string $scheme = null;
    private ?string $user = null;
    private ?string $pass = null;
    private ?string $host = null;
    private ?int $port = null;
    private ?string $path = null;
    private array $query = [];
    private ?string $fragment = null;

    public function __construct(?string $url = null)
    {
        if (is_null($url)) {
            $url = $_SERVER['REQUEST_SCHEME'] . '://' . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];
        }

        foreach (parse_url($url) as $k => $v) {
            switch ($k) {
                case 'query':
                    foreach (explode('&', $v) as $q) {
                        list($a, $b) = explode('=', $q);
                        $this->query[$a] = $b;
                    }
                    break;

                default:
                    $this->$k = $v;
                    break;
            }
        }
    }

    /**
     * Adds or replaces a GET parameter
     *
     * @param $k The GET parameter
     * @param $v Its value
     */
    public function setQuery(string $k, string $v): static
    {
        $this->query[$k] = $v;
        return $this;
    }

    /**
     * Deletes a GET parameter
     *
     * @param $k The GET parameter
     */
    public function deleteQuery(string $k): static
    {
        if (array_key_exists($k, $this->query)) {
            unset($this->query[$k]);
        }
        return $this;
    }

    /**
     * Redirects to the URL
     */
    public function redirect(): never
    {
        header('Location: ' . $this);
        exit;
    }

    /**
     * Returns the full URL
     */
    public function get(): string
    {
        $url = [];

        if ($this->scheme) {
            $url[] = $this->scheme . '://';
        }

        if ($this->user) {
            $url[] = $this->user;
        }

        if ($this->pass) {
            $url[] = ':' . $this->pass;
        }

        if ($this->user || $this->pass) {
            $url[] = '@';
        }

        if ($this->host) {
            $url[] = $this->host;
        }

        if ($this->port) {
            $url[] = ':' . $this->port;
        }

        $url[] = $this->path;

        if ($this->query) {
            $url[] = '?';
            $s = [];
            foreach ($this->query as $k => $v) {
                $s[] = $k . '=' . $v;
            }
            $url[] = implode('&', $s);
        }

        if ($this->fragment) {
            $url[] = '#' . $this->fragment;
        }

        return implode('', $url);
    }

    public function __toString()
    {
        return $this->get();
    }
}

/**
 * Personnalized JWT class based on the Firebase one, to manipulate Json Web Tokens, to store them in a cookie and in the database, and so on
 */
class JWT
{
    private ?int $tokenId = null;
    private ?int $userId = null;

    private static string $key;
    private static PDO $db;

    /**
     * @param string|null $data If null, we recover the JWT from the cookie, otherwise we create it. (Designed for client/authServer requests)
     *                          If string, we try to decode it to get the corresponding tokenId. (Designed for server/authServer requests)
     */
    public function __construct(?string $data = null)
    {
        # If we do not provide $data, then we want to recover the JWT from the cookie or we want to create a new one
        # To be used in client/authServer requests

        if (is_null($data)) {

            # We try to recover the token from the cookie

            if (isset($_COOKIE['jwt'])) {
                $this->tokenId = static::decode($_COOKIE['jwt']);
            }

            # If we fail, we create a new one and we save it in the cookie and in the database

            if (is_null($this->tokenId)) {
                $sth = static::$db->prepare('INSERT INTO `token` (`userId`) VALUES (NULL)');
                $sth->execute();
                $this->tokenId = static::$db->lastInsertId();
                $this->saveCookie();
            }
        }

        # If not, we recover the token from a server/authServer requests

        else {
            $this->tokenId = static::decode($data);
        }
    }

    /**
     * Initializing the database variable $db and the $key
     */
    public static function init($db): void
    {
        # Let's store the database

        static::$db = &$db;

        # If we have no key, we create it randomly

        if (!file_exists(APP_PATH . '/jwt_key.txt')) {
            $bank = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $jwt_key = '';
            for ($i = 0; $i < 20; $i++) {
                $jwt_key.= $bank[rand(0, strlen($bank))];
            }
            file_put_contents(APP_PATH . '/jwt_key.txt', $jwt_key);
        }

        # Let's store the key

        static::$key = trim(file_get_contents(APP_PATH . '/jwt_key.txt'));
    }

    /**
     * A token is valid if it has a tokenId and if this tokenId is well stored in the database.
     */
    public function isValid(): bool
    {
        if (is_null($this->tokenId)) {
            return false;
        }

        $sth = static::$db->prepare('SELECT * FROM `token` WHERE `id` = :id');
        $sth->bindParam('id', $this->tokenId);
        $sth->execute();

        $results = $sth->fetchAll(PDO::FETCH_CLASS);
        if (1 === count($results)) {
            $this->userId = $results[0]->userId;
            return true;
        } else {
            return false;
        }
    }

    public function getTokenId(): ?int
    {
        return $this->tokenId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Generates the JWT from its payload
     */
    public function encode(): string
    {
        return FJWT::encode(['tokenId' => $this->tokenId], static::$key, 'HS256');
    }

    /**
     * @return integer|null Returns the tokenId of a JWT if it is valid and if it exists, NULL otherwise
     */
    public static function decode(string $jwt): ?int
    {
        # This method overwrites the Firebase one, it is adapted to our needs

        $timestamp = time();

        $tks = explode('.', $jwt);

        if (count($tks) != 3) {
            return null;
        }

        list($headb64, $bodyb64, $cryptob64) = $tks;

        if (null === ($header = FJWT::jsonDecode(FJWT::urlsafeB64Decode($headb64)))) {
            return null;
        }

        if (null === $payload = FJWT::jsonDecode(FJWT::urlsafeB64Decode($bodyb64))) {
            return null;
        }

        if (!isset($payload->tokenId)) {
            return null;
        }

        if (false === ($sig = FJWT::urlsafeB64Decode($cryptob64))) {
            return null;
        }

        if (empty($header->alg)) {
            return null;
        }

        if (empty(FJWT::$supported_algs[$header->alg])) {
            return null;
        }

        if (!FJWT::constantTimeEquals('HS256', $header->alg)) {
            return null;
        }

        if (!hash_equals(hash_hmac('SHA256', "$headb64.$bodyb64", static::$key, true), $sig)) {
            return null;
        }

        if (isset($payload->nbf) && $payload->nbf > ($timestamp + FJWT::$leeway)) {
            return null;
        }

        if (isset($payload->iat) && $payload->iat > ($timestamp + FJWT::$leeway)) {
            return null;
        }

        if (isset($payload->exp) && ($timestamp - FJWT::$leeway) >= $payload->exp) {
            return null;
        }

        return $payload->tokenId;
    }

    /**
     * Saves the JWT in a cookie
     */
    public function saveCookie(): void
    {
        setcookie('jwt', $this->encode(), time() + 3600);
    }

    /**
     * Associates the userId with the JWT in the database
     */
    public function updateUser(int $userId): void
    {
        $sth = static::$db->prepare('UPDATE `token` SET `userId` = :userId WHERE `id` = :id');
        $sth->bindParam('id', $this->tokenId);
        $sth->bindParam('userId', $userId);
        $sth->execute();
    }

    /**
     * Dissociates the userId from the JWT in the database
     */
    public function logout(): void
    {
        if ($this->tokenId) {
            $sth = static::$db->prepare('UPDATE `token` SET `userId` = NULL WHERE `id` = :id');
            $sth->bindParam('id', $this->tokenId);
            $sth->execute();
        }
    }
}

JWT::init($db);
