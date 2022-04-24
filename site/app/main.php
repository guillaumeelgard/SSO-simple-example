<?php

session_start();

# Defining our constants

    define('APP_PATH', __DIR__);

# Composer

    require_once APP_PATH . '/vendor/autoload.php';


# Initializing some stuff

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

    foreach($websites as $k => $website)
    {
        if(preg_replace('/^.+\//', '', $website) == ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST']))
        {
            $kSite = $k;
            break;
        }
    }

# Let's find some helpers

    require_once APP_PATH . '/helpers.php';
    require_once APP_PATH . '/api.php';

# Go! It's kind of a very very simple controller right here:

    # For any action

        if (isset($_GET['action'])) {

            $file = APP_PATH . '/actions/' . $_GET['action'] . '.php';

            # If the file action exists, we load it

                if (file_exists($file)) {
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

    # Otherwise let's load our view but first, token

        } else {

            # If we already have a token, let's verify it

                if (isset($_SESSION['jwt'])) {

                    $result = $api->verifyToken($_SESSION['jwt']);

                    # If it's legit, let's store info in session then go to the view

                        if ($result['success']) {
                            $_SESSION['user'] = $result['user'];
                            $_SESSION['tokenId'] = $result['tokenId'];
                        }
                    
                    # If it's not, let's get a new one (will redirect to auth server)

                        else {
                            $api->getNewToken();
                        }
                }

            # If we don't, maybe we come with a JWT in a GET parameter

                elseif (isset($_GET['jwt'])) {

                    $result = $api->verifyToken($_GET['jwt']);

                    # Let's verify it. If it's legit, let's store info in session then go to the view

                        if ($result['success']) {
                            $_SESSION['user'] = $result['user'];
                            $_SESSION['jwt'] = $_GET['jwt'];
                            $_SESSION['tokenId'] = $result['tokenId'];
                            (new Url())->deleteQuery('jwt')->redirect(); 
                        }
                    
                    # If it's not, let's get a new one (will redirect to auth server)
                        
                        else {
                            $api->getNewToken();
                        }
                }
                
            # If not, let's create a new token (will redirect to auth server)
                
                else {
                    $api->getNewToken();
                }

            # Finally, the view

                $page = $_SESSION['user'] ? 'logout' : 'login';
                require_once APP_PATH . '/views/template.php';
        }
