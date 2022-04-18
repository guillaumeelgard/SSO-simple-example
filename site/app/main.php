<?php

session_start();
define('APP', getenv('APP'));
define('APP_PATH', __DIR__);

require_once APP_PATH . '/vendor/autoload.php';
require_once APP_PATH . '/helpers.php';

if(!file_exists(APP_PATH . '/websites.json'))
{
    copy(APP_PATH . '/websites.sample.json', APP_PATH . '/websites.json');
}

if(!file_exists(APP_PATH . '/authAddress.txt'))
{
    copy(APP_PATH . '/authAddress.sample.txt', APP_PATH . '/authAddress.txt');
}

$websites = array_map(fn($a) => trim($a, '/'), json_decode(file_get_contents(APP_PATH . '/websites.json')));
$authAddress = trim(trim(file_get_contents(APP_PATH . '/authAddress.txt'), '/'));

require_once APP_PATH . '/api.php';

p($websites);
d($_SERVER);

foreach($websites as $k => $website)
{
    if($website == $_SERVER['REQUEST_SCHEME'] . '://' . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST']))
    {
        $kSite = $k;
        break;
    }
}

if (isset($_GET['action'])) {
    $file = APP_PATH . '/actions/' . $_GET['action'] . '.php';
    if (file_exists($file)) {
        require_once($file);
    } else {
        err404();
    }
} else {
    if (isset($_SESSION['jwt'])) {
        $result = $api->verifyToken($_SESSION['jwt']);
        if ($result['success']) {
            $_SESSION['user'] = $result['user'];
            $_SESSION['tokenId'] = $result['tokenId'];
        } else {
            $api->getNewToken();
        }
    } else {
        if (isset($_GET['jwt'])) {
            $result = $api->verifyToken($_GET['jwt']);
            if ($result['success']) {
                $_SESSION['user'] = $result['user'];
                $_SESSION['jwt'] = $_GET['jwt'];
                $_SESSION['tokenId'] = $result['tokenId'];
                (new Url())->deleteQuery('jwt')->redirect();
            } else {
                $api->getNewToken();
            }
        } else {
            $api->getNewToken();
        }
    }

    $less = new lessc();
    $less->compileFile(APP_PATH . '/style.less', APP_PATH . '/../html/style.css');

    $page = $_SESSION['user'] ? 'logout' : 'login';
    require_once APP_PATH . '/views/template.php';
}
