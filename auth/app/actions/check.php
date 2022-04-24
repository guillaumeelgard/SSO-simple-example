<?php

/**
 * We recover the JWT from the cookie, otherwise we create a new one.
 * In any case, we redirect to the provided URL adding the JWT in a GET parameter.
 */

$jwt = new JWT();
$url = new Url($_GET['to']);
$url->setQuery('jwt', $jwt->encode());
$url->redirect();
