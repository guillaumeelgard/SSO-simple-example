<?php

# Defining our constants

    define('APP_PATH', __DIR__);

# Composer

    require_once APP_PATH . '/vendor/autoload.php';

# Initializing some stuff

    # apps addresses

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

    # Database

        if(!file_exists(__DIR__ . '/database.sqlite'))
        {
            copy(__DIR__ . '/database.sample.sqlite', __DIR__ . '/database.sqlite');
        }
        
        $db = new PDO('sqlite:' . __DIR__ . '/database.sqlite');

# Let's find some helpers

    require_once APP_PATH . '/helpers.php';

# Go! It's kind of a very very simple controller right here:

    # For any action

        if (isset($_GET['action'])) {

            $file = APP_PATH . '/actions/' . $_GET['action'] . '.php';

            # If the file action exists, we load it

                if (file_exists($file)) {
                    header('Content-Type: application/json; charset=utf-8');
                    require_once($file);
                }
            
            # If not, 404
            
                else {
                    header('HTTP/1.0 404 Not Found');
                    echo json_encode([
                        'success' => false,
                        'err_type' => 404,
                    ]);
                    exit;
                }
        }

    # Otherwise let's make it simple and let's load our unique view

        else {
            require_once APP_PATH . '/views/home.php';
        }
