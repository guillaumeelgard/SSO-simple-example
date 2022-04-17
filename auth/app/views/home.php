<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="/style.css">
    <title>Auth</title>
    <style>

        :root {
            --bg-color: darkgreen;
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
                        <a class="nav-link" href="<?=$website?>">Site <?=$k + 1?></a>
                    </li>
                    <?php
                }

                ?>
                <li class="nav-item">
                    <a class="nav-link active" href="http://localhost:8300/">Auth</a>
                </li>
            </ul>
        </div>
    </nav>
    <section>
        <div class="container mt-4 bg-dark text-secondary p-3">
            <h4>$_COOKIE</h4>
            <pre><?php

                if (isset($_COOKIE['jwt'])) {
                    $jwt = new JWT();

                    $display = [
                        'JWT' => $_COOKIE['jwt'],
                    ];

                    if ($jwt->isValid()) {
                        $display['JWT valide'] = 'oui';
                        $display['tokenId'] = $jwt->getTokenId();
                        $display['userId'] = $jwt->getUserId() ?? 'NULL';
                    } else {
                        $display['JWT valide'] = 'non';
                    }

                    echo implode("\n", array_map_assoc($display, function ($k, $v) {
                        return "$k : $v";
                    }));
                } else {
                    echo 'Pas de cookie';
                }

            ?></pre>
        </div>
        <div class="container mt-4 bg-dark text-secondary p-3">
            <h4>Database</h4>
            <pre><?php

            $sth = $db->prepare('SELECT * FROM `sqlite_master` WHERE `type`="table" AND `name` NOT LIKE "sqlite_%"');
            $sth->execute();
            foreach ($sth->fetchAll(PDO::FETCH_OBJ) as $table) {
                
                $select = $db->query('SELECT * FROM `' . $table->name . '`');
                $nbCols = $select->columnCount();

                ?><table>
                    <thead>
                        <tr>
                            <th colspan="<?=$nbCols?>"><?=htmlspecialchars(mb_strtoupper($table->name))?></th>
                        </tr>
                        <tr>
                            <?php

                            for ($i = 0; $i < $nbCols; $i++) {
                                ?>
                                <th><?=htmlspecialchars($select->getColumnMeta($i)['name'])?></th>
                                <?php
                            }
                            
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        foreach ($select->fetchAll(PDO::FETCH_OBJ) as $r) {
                            ?>
                            <tr>
                                <?php

                                foreach ($r as $v) {
                                    ?>
                                    <td><?=is_null($v) ? '<em>NULL</em>' : htmlspecialchars($v)?></td>
                                    <?php
                                }
                                
                                ?>
                            </tr>
                            <?php
                        }
                        
                        ?>
                    </tbody>
                </table><?php
            }

            ?></pre>
        </div>
    </section>
</body>
</html>
