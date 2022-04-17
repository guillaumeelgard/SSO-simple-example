<?php

/**
 * Vérifie le JWT proposé et retourne l'utilisateur en cas de succès
 *
 * @var PDO $db
 */

$data = json_decode(file_get_contents('php://input'));

if (! isset($data->jwt)) {
    exit(json_encode([
        'success' => false,
    ]));
}

$jwt = new JWT($data->jwt);

if (! $jwt->isValid()) {
    exit(json_encode([
        'success' => false,
        'tokenId' => $jwt->getTokenId(),
    ]));
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
