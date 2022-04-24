<?php

/**
 * Let's verify our credentials. If they are valid, we can store the user and the JWT provided.
 * 
 * @var Api $api
 */

$data = json_decode(file_get_contents('php://input'), true);

$result = $api->login($data['login'], $data['password']);

if ($result['success']) {
    $_SESSION['user'] = $result['user'];
    $_SESSION['jwt'] = $result['jwt'];

    echo json_encode([
        'success' => true,
        'jwt' => $result['jwt'],
    ]);
} else {
    echo json_encode([
        'success' => false,
    ]);
}
