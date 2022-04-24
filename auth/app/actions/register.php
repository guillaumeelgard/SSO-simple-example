<?php

/**
 * Checks the JWT provided in a GET parameter then saves it in a cookie then redirects to the provided URL.
 */

$jwt = new JWT($_GET['jwt']);
if ($jwt->isValid()) {
    $jwt->saveCookie();
}

header('Location: ' . $_GET['to']);
