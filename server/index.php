<?php

require_once 'lib/limonade.php';

function get_code($type) {
    $codes = array(
        'success' => array(
            'code' => 200,
            'msg' => 'OK',
        ),
        'fail' => array(
            'code' => 400,
            'msg' => 'Bad Request',
        ),
    );

    // no type
    if (!isset($codes[$type])) {
        return $codes['fail'];
    }

    return $codes[$type];

}

function get_pdo() {

    $dbh = 'mysql:host=localhost;dbname=db_katte;charset=utf8';

    $pdo = new PDO(
        $dbh,
        'katte',
        'kattepass',
        array(PDO::ATTR_EMULATE_PREPARES => false)
    );
    return $pdo;
}

dispatch('/', 'index');
function index() {
    $pdo = get_pdo();

    $query = 'select * from katte_items inner join katte_user on katte_items.mention_user_id = katte_user.id';
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = 'select katte_user.user_name,katte_items.image_name from katte_items inner join katte_user on katte_items.katte_user_id = katte_user.id;';
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result_from_user = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $item_info = array();
    foreach ($result_from_user as $row) {
        $item_info[$row['image_name']] = $row['user_name'];
    }

    set('items', $result);
    set('item_info', $item_info);
    return html('index.html');
}

dispatch_get('/sell', 'sell');
dispatch_post('/sell', 'sell');
function sell() {
    if (
        isset($_REQUEST['item_name'])
            &&
        isset($_REQUEST['item_description'])
            &&
        isset($_REQUEST['mention_user_name'])
            &&
        isset($_REQUEST['price'])
    ) {

        try {
            $pdo = get_pdo();

            $sql = 'select id from katte_user where user_name = :user_name';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':user_name' => $_REQUEST['mention_user_name']));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // no mention user
            if (!isset($result['id'])) {
                return html('sell.html');
            }

            $mention_user_id = (int) $result['id'];
            // get user
            $sql = 'select id from katte_user where user_hash = :user_hash';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':user_hash' => $_REQUEST['user_hash']));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!isset($result['id'])) {
                return html('sell.html');
            }
            $katte_user_id = (int) $result['id'];


            $item_name = $_REQUEST['item_name'];
            $item_description = $_REQUEST['item_description'];
            $price = $_REQUEST['price'];
            $image_name = hash('sha256', $item_name . $item_description);

            // move
            move_uploaded_file($_FILES["image"]["tmp_name"], "/tmp/" . $image_name);

            chmod("/tmp/" .$image_name, 0644);
            
            $pdo->beginTransaction();
            // create hash
            $timestamp = strftime('%Y-%m-%d %H:%M:%S', time());
            $insert_query = 'insert into katte_items set
                katte_user_id = :katte_user_id,
                item_name = :item_name,
                item_description = :item_description,
                image_name = :image_name,
                price = :price,
                mention_user_id = :mention_user_id,
                created_at = :created_at,
                updated_at = :updated_at
            ';
            $stmt = $pdo->prepare($insert_query);
            $params = array(
                ':katte_user_id' => $katte_user_id,
                ':item_name' => $item_name,
                ':item_description' => $item_description,
                ':image_name' => $image_name,
                ':mention_user_id' => $mention_user_id,
                ':price' => $price,
                ':created_at' => $timestamp,
                ':updated_at' => $timestamp,
            );
            $ret = $stmt->execute($params);
            $pdo->commit();

            redirect("/");
        } catch (PDOException $e) {
            print $e->getMessage();
            return html('sell.html');
        }

    } else {
        return html('sell.html');
    }
}

dispatch('/detail', 'detail');
function detail() {
    return html('detail.html');
}

// ==== API

dispatch('/api_regist', 'regist');
function regist() {
    if (!isset($_REQUEST['name']) || !isset($_REQUEST['grade']) || !isset($_REQUEST['regist_id'])) {
        return json(
            get_code('fail')
        );
    }
    $user_name = $_REQUEST['name'];
    $user_grade = (int) $_REQUEST['grade'];
    $regist_id = $_REQUEST['regist_id'];

    $pdo = get_pdo();

    $cnt_query = 'select user_hash as cnt from katte_user where user_name = :user_name';
    $stmt = $pdo->prepare($cnt_query);
    $stmt->execute(array(':user_name' => $user_name));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = null;
    $pdo = null;
    if (isset($result['user_hash'])) {
        $result = get_code('success');
        $result['body'] = array(
            'hash' => $result['user_hash']
        );
        return json($result);
    }

    try {
        $pdo = get_pdo();
        $pdo->beginTransaction();
        // create hash
        $user_hash = hash('sha256', $user_name . $user_grade . $regist_id);
        $timestamp = strftime('%Y-%m-%d %H:%M:%S', time());
        $insert_query = 'insert into katte_user set
                        user_name = :user_name,
                        user_hash = :user_hash,
                        registration_id = :registration_id,
                        grade = :grade,
                        created_at = :created_at,
                        updated_at = :updated_at
                    ';
        $stmt = $pdo->prepare($insert_query);
        $params = array(
            ':user_name' => $user_name,
            ':user_hash' => $user_hash,
            ':registration_id' => $regist_id,
            ':grade' => $user_grade,
            ':created_at' => $timestamp,
            ':updated_at' => $timestamp,
        );
        $ret = $stmt->execute($params);
        $pdo->commit();

        $result = get_code('success');
        $result['body'] = array(
            'hash' => $user_hash,
        );

        return json($result);
    } catch (PDOException $e) {
        return json(get_code('fail'));
    }
}

dispatch('/api_notify', 'notify');
function notify() {
    return json(
        array(
            'is_notify' => true
        )
    );
}

dispatch('/api_buy', 'buy');
function buy() {
    return json(
        array(
            'is_success' => true
        )
    );
}


run();
