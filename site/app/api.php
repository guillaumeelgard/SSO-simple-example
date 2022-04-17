<?php

$api = new class () {
    private string $internalUrl = 'http://host.docker.internal:8300';
    private string $externalUrl = 'http://localhost:8300';

    public function call(string $uri, ?array $post = null, bool $json_encode = true): stdClass
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

        return $json_encode ? json_decode($content) : $content;
    }

    public function getNewToken(): never
    {
        unset($_SESSION['jwt']);
        unset($_SESSION['user']);

        $currentFullUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $this->externalUrl . '/?action=check&to=' . urlencode($currentFullUrl));
        exit;
    }

    public function verifyToken(string $jwt): stdClass
    {
        return $this->call('/?action=verify', ['jwt' => $jwt]);
    }

    public function login(string $login, string $password): stdClass
    {
        return $this->call('/?action=login', [
            'login' => $login,
            'password' => $password,
            'jwt' => $_SESSION['jwt'],
        ]);
    }
};
