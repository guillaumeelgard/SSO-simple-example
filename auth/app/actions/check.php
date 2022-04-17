<?php

/**
 * On récupère s'il existe le JWT en cookie, sinon on en crée un nouveau.
 * Puis on redirige vers l'URL demandée en ajoutant le JTW en $_GET
 */

(new Url($_GET['to']))->setQuery('jwt', (new JWT())->encode())->redirect();
