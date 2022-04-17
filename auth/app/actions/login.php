<?php

/**
 * On check le login et le mot de passe. S'ils sont bons on associe le userId au token existant ou bien à un nouveau s'il n'en existe pas encore.
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
