<?php

/**
 * Vérifie le JWT passé en $_GET et l'enregistre en cookie puis redirige vers l'URL demandée
 */

$jwt = new JWT($_GET['jwt']);
if ($jwt->isValid()) {
    $jwt->saveCookie();
}

header('Location: ' . $_GET['to']);
