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

dispatch('/', 'index');
function index() {
  return html('index.html');
}

dispatch('/sell', 'sell');
function sell() {
  return html('sell.html');
}

run();
