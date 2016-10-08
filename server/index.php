<?php

require_once 'lib/limonade.php';

function get_pdo() {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=db_katte_honda;charset=utf8',
        'katte',
        'kattepass',
        array(PDO::ATTR_EMULATE_PREPARES => false)
    );
    return $pdo;
}

dispatch('/', 'hello');
function hello() {
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT * FROM max_mention_count_by_grade");
    $count = $stmt->rowCount();
    return 'Hello world! honda' . $count . 'h';
}

run();
