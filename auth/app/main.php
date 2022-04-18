<?php

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
$authAddress = trim(trim(file_get_contents(APP_PATH . '/authAddress.txt')), '/');

if (isset($_GET['action'])) {
    $file = APP_PATH . '/actions/' . $_GET['action'] . '.php';
    if (file_exists($file)) {
        header('Content-Type: application/json; charset=utf-8');
        require_once($file);
    } else {
        err404();
    }
} else {
    $less = new lessc();
    $less->compileFile(APP_PATH . '/style.less', APP_PATH . '/../html/style.css');

    require_once APP_PATH . '/views/home.php';
}
