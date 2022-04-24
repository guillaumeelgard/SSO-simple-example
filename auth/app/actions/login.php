<?php

/**
 * Let's check login and password. If they are correct, we associate the userId to the existing token or to a new one if we don't have any yet.
 *
 * @var PDO $db
 */

$data = json_decode(file_get_contents('php://input'));

$sth = $db->prepare('SELECT * FROM `user` WHERE `login` = :login AND `password` = :password');
$sth->bindParam('login', $data->login);
$sth->bindParam('password', $data->password);
$sth->execute();

if ($user = $sth->fetch(PDO::FETCH_OBJ)) {
    $jwt = new JWT($data->jwt);
    if (! $jwt->isValid()) {
        $jwt = new JWT();
    }

    $jwt->updateUser($user->id);

    echo json_encode([
        'success' => true,
        'user' => $user,
        'jwt' => $jwt->encode(),
    ]);
} else {
    echo json_encode([
        'success' => false,
    ]);
}
