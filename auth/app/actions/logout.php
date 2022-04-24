<?php

/**
 * We dissociate the JWT from the userId then we redirect to the provided URL.
 */

if (! isset($_COOKIE['jwt'])) {
    header('Location: ' . $_GET['to']);
    exit;
}

$jwt = new JWT();
if ($jwt->isValid()) {
    $jwt->logout();
}

header('Location: ' . $_GET['to']);
exit;
