<?php

/**
 * This API is our link with the auth server
 */
class Api {
    
    /**
     * The internal URL is used by the server
     *
     * @var string
     */
    private string $internalUrl = 'http://host.docker.internal:8300';
    
    /**
     * The external URL is transmitted to be used by the client
     *
     * @var string
     */
    private string $externalUrl;

    public function __construct(string $externalUrl)
    {
        $this->externalUrl = $externalUrl;
    }

    public function call(string $uri, ?array $post = null): array
    {
        $header = [];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->internalUrl . $uri);

        if (! is_null($post)) {
            $post_string = json_encode($post);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);

            $header[] = 'Content-Length: ' . strlen($post_string);
            $header[] = 'Content-Type: application/json';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        curl_close($ch);
        return json_decode($content, true);
    }

    /**
     * We are being redirected to the auth server. We will be redirected to the same URL but with a new JWT in a GET parameter.
     */
    public function getNewToken(): never
    {
        unset($_SESSION['jwt']);
        unset($_SESSION['user']);

        $currentFullUrl = $_SERVER['REQUEST_SCHEME'] . '://' . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];
        header('Location: ' . $this->externalUrl . '/?action=check&to=' . urlencode($currentFullUrl));
        exit;
    }

    /**
     * Let's verify if our JWT is valid or not, and if it is, we get the associated tokenId and if we're connected, the user info
     *
     * @param  string $jwt
     * @return array{success: boolean, user: object|null, tokenId: int}
     */
    public function verifyToken(string $jwt): array
    {
        return $this->call('/?action=verify', ['jwt' => $jwt]);
    }

    /**
     * Let's verify if our credentials are valid or not, and if they are, we get the associated user info and the jwt we can store
     *
     * @return array{success: boolean, user: object|null, jwt: string}
     */
    public function login(string $login, string $password): array
    {
        return $this->call('/?action=login', [
            'login' => $login,
            'password' => $password,
            'jwt' => $_SESSION['jwt'],
        ]);
    }
};

$api = new Api($authAddress);
