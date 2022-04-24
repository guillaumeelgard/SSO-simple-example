<?php

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
            foreach($this->query as $k => $v)
            {
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