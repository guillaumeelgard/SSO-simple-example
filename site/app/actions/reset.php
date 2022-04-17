<?php

unset($_SESSION['jwt']);
unset($_SESSION['user']);
header('Location: /');
