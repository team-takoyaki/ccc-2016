<?php

$server_key = 'SERVE_KEY';
$dbh = 'mysql:host=localhost;dbname=db_katte;charset=utf8';

$pdo = new PDO(
    $dbh,
    'katte',
    'kattepass',
    array(PDO::ATTR_EMULATE_PREPARES => false)
);

$sql = 'select registration_id, item_id from (select katte_user.registration_id, mentions.item_id from mentions inner join katte_user on katte_user.id = mentions.to_katte_user_id) as t inner join katte_items on t.item_id = katte_items.id where katte_items.is_purchased = 0;';

$stmt = $pdo->prepare($sql);

$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    $item_id = $row['item_id'];
    $registration_id = $row['registration_id'];
    $cmd = 'curl --header "Authorization: key=' . $sever_key . '" '
        . ' --header Content-Type:"application/json" '
        . ' https://fcm.googleapis.com/fcm/send '
        . '-d "{ '
        . '     \"data\": {'
        . '        \"title\": \"北野ビヨンド\", '
        . '        \"message\": \"メンションされたぞばかやろう\", '
        . '       \"item_id\": \"' . $item_id . '\"'
        . '   }, '
        . '    \"to\": \"' . $registration_id . '\"'
        . ' }'
        . '"';
    exec($cmd);
}
