<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <script src="/scripts.js"></script>
    <link rel="stylesheet" href="/style.css">
    <title>Site <?=$kSite + 1?></title>
    <style>

        <?php

        switch ($kSite) {
            case 0:
                $bgColor = 'orange';
                break;

            case 1:
                $bgColor = 'blueviolet';
                break;
                    
            case 2:
                $bgColor = 'pink';
                break;
                
            case 3:
                $bgColor = 'teal';
                break;
        }

        ?>

        :root {
            --bg-color: <?=$bgColor?>;
        }

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php

                foreach($websites as $k => $website)
                {
                    ?>
                    <li class="nav-item">
                        <a class="nav-link <?=$kSite == $k ? 'active' : ''?>" href="<?=$website?>">Site <?=$k + 1?></a>
                    </li>
                    <?php
                }

                ?>
                <li class="nav-item">
                    <a class="nav-link" href="http://localhost:8300/">Auth</a>
                </li>
            </ul>
        </div>
    </nav>
    <section>
        <?php

        require $page . '.php';
        $jsFile = __DIR__ . '/' . $page . '.js';
        if (file_exists($jsFile)) {
            ?><script><?php require $jsFile; ?></script><?php
        }

        ?>
        <pre class="container mt-4 bg-dark text-secondary p-3">
            <h4>$_SESSION</h4>
            <p><?php print_r($_SESSION); ?></p>
        </pre>
    </section>
</body>
</html>
