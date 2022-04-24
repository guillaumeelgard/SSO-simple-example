<?php

/**
 * Checks the provided JWT and returns the user if it's successful
 *
 * @var PDO $db
 */

$data = json_decode(file_get_contents('php://input'));

if (! isset($data->jwt)) {
    echo json_encode([
        'success' => false,
    ]);
    exit;
}

$jwt = new JWT($data->jwt);

if (! $jwt->isValid()) {
    echo json_encode([
        'success' => false,
    ]);
    exit;
}

$user = null;
if ($jwt->getTokenId()) {
    $sth = $db->prepare('SELECT * FROM `user` WHERE `id` = :id');
    $tokenId = $jwt->getUserId();
    $sth->bindParam('id', $tokenId);
    $sth->execute();
    $user = $sth->fetch(PDO::FETCH_OBJ);
}

echo json_encode([
    'success' => true,
    'user' => $user,
    'tokenId' => $jwt->getTokenId(),
]);
