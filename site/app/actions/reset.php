<?php

/**
 * Let's forget who we are!
 */

unset($_SESSION['jwt']);
unset($_SESSION['user']);
header('Location: /');
exit;
