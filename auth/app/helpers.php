<?php

use Firebase\JWT\JWT as FJWT;

global $db;
$db = new PDO('sqlite:' . __DIR__ . '/database.sqlite');

function err404()
{
    header('HTTP/1.0 404 Not Found');
    echo json_encode([
        'success' => false,
        'err_type' => 404,
    ]);
    exit;
}

function p($any)
{
    echo '<pre>';
    print_r($any);
    echo '</pre>';
}

function d($any)
{
    p($any);
    exit;
}

function array_map_assoc(array $array, callable $callback): array
{
    $return = [];
    foreach ($array as $k => $v) {
        $return[] = $callback($k, $v);
    }
    return $return;
}

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
            $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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

    public function setQuery(string $k, string $v): self
    {
        $this->query[$k] = $v;
        return $this;
    }

    public function deleteQuery(string $k): self
    {
        if (array_key_exists($k, $this->query)) {
            unset($this->query[$k]);
        }
        return $this;
    }

    public function redirect(): void
    {
        header('Location: ' . $this);
        exit;
    }

    public function get()
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
            $url[] = implode('&', array_map_assoc($this->query, function ($a, $b) {
                return $a . '=' . $b;
            }));
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

class JWT
{
    private ?int $tokenId = null;
    private ?int $userId = null;

    private static string $key = '7WVQWzdclux2zF3ZCYZL';
    private static PDO $db;

    public function __construct(?string $data = null)
    {
        if (!isset(self::$db)) {
            global $db;
            self::$db = &$db;
        }

        # Si on ne fournit pas de $data, c'est qu'on veut récupérer le JWT enregistré en cookie ou bien en créer un nouveau
        # À utiliser dans des requêtes client <=> authServer

        if (is_null($data)) {
            # On récupère le JWT en cookie

            if (isset($_COOKIE['jwt'])) {
                $this->tokenId = self::decode($_COOKIE['jwt']);
            }

            # Si ça n'a pas marché, on en crée un nouveau et on l'enregistre en BDD et en cookie

            if (is_null($this->tokenId)) {
                $sth = self::$db->prepare('INSERT INTO `token` (`userId`) VALUES (NULL)');
                $sth->execute();
                $this->tokenId = self::$db->lastInsertId();
                $this->saveCookie();
            }
        }

        # Sinon on récupère un JWT à partir d'un échange server <=> authServer

        else {
            $this->tokenId = self::decode($data);
        }
    }

    /**
     * Un JWT est valide s'il a bien un tokenId et si ce tokenId existe bien en BDD.
     *
     * @return boolean
     */
    public function isValid(): bool
    {
        if (is_null($this->tokenId)) {
            return false;
        }

        $sth = self::$db->prepare('SELECT * FROM `token` WHERE `id` = :id');
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
     * Génère le JWT à partir de son payload
     *
     * @return string
     */
    public function encode(): string
    {
        return FJWT::encode(['tokenId' => $this->tokenId], self::$key, 'HS256');
    }

    /**
     * Retourne le tokenId d'un JWT s'il est valide et s'il existe, NULL sinon
     *
     * @param string $jwt
     * @return integer|null
     */
    public static function decode(string $jwt): ?int
    {
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

        if (!hash_equals(hash_hmac('SHA256', "$headb64.$bodyb64", self::$key, true), $sig)) {
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
     * Enregistre le JWT dans un cookie
     *
     * @return void
     */
    public function saveCookie(): void
    {
        setcookie('jwt', $this->encode(), time() + 3600);
    }

    /**
     * Associe en base de données le userId au JWT
     *
     * @param integer $userId
     * @return void
     */
    public function updateUser(int $userId): void
    {
        $sth = self::$db->prepare('UPDATE `token` SET `userId` = :userId WHERE `id` = :id');
        $sth->bindParam('id', $this->tokenId);
        $sth->bindParam('userId', $userId);
        $sth->execute();
    }

    /**
     * Dissocie en base de données le userId du JWT
     *
     * @return void
     */
    public function logout(): void
    {
        if ($this->tokenId) {
            $sth = self::$db->prepare('UPDATE `token` SET `userId` = NULL WHERE `id` = :id');
            $sth->bindParam('id', $this->tokenId);
            $sth->execute();
        }
    }
}
