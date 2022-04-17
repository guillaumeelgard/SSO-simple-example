<?php

/**
 * On dissocie le JWT du userId puis on redirige vers l'URL demandée
 *
 * @var PDO $db
 * @var string $secret
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
