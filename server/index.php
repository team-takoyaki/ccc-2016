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
    return html('index.html');
}

dispatch('/sell', 'sell');
function sell() {
    return html('sell.html');
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
