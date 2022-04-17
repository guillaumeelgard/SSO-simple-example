<?php

$result = $api->login(postData('login'), postData('password'));

if ($result->success) {
    $_SESSION['user'] = $result->user;
    $_SESSION['jwt'] = $result->jwt;

    echo json_encode([
        'success' => true,
        'jwt' => $result->jwt,
    ]);
} else {
    echo json_encode([
        'success' => false,
    ]);
}
