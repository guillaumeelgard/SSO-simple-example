<?php

function err404()
{
    header('HTTP/1.0 404 Not Found');
    echo json_encode([
        'success' => false,
        'err_type' => 404,
    ]);
    exit;
}

function p(mixed $any): void
{
    echo '<pre>';
    print_r($any);
    echo '</pre>';
}

function d(mixed $any): never
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

    public function setQuery(string $k, string $v): static
    {
        $this->query[$k] = $v;
        return $this;
    }

    public function deleteQuery(string $k): static
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

function postData(?string $key = null): mixed
{
    $data = (array) json_decode(file_get_contents('php://input'), true);

    if (is_null($key)) {
        return $data;
    }

    if (array_key_exists($key, $data)) {
        return $data[$key];
    }

    return null;
}
