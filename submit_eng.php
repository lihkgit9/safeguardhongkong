<?php
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE);
header("Content-Type:application/json; charset=utf-8");

$end_time = strtotime('2019-08-30 23:59:59');
$time = time();
if ($time >= $end_time) {
    $json_array = array(
        'errorcode' => -45,
        'message' => 'The activity has ended'
    );
    echo json_encode($json_array);
    exit ();
}

define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);

if (IS_POST) {
    define('FZZINDEXROOT', dirname(__FILE__));
    define("POST_INTERVAL", 180);

    $orig_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    if (empty($orig_url) || strtok($orig_url, '?') !== 'https://joint-sign.safeguardhongkong.hk/sign_eng.html') {
        $json_array = array(
            'errorcode' => -3,
            'message' => '非法訪問'
        );
        echo json_encode($json_array);
        exit();
    }

    include_once FZZINDEXROOT . '/comm/verify.php';
    // include_once FZZINDEXROOT . '/comm/HKID.php';
    include_once FZZINDEXROOT . '/Model/Fzz_dbc.php';

    $json_array = array();
    // Data verify
    $allowed_fields = array('username', 'sector', 'hkcode', 'tel', 'yzm', 'token');
    $dataArray = post_check($_POST, $allowed_fields);

    $verify_rt = verify_fzzform($dataArray);

    // captcha code
    if ($verify_rt == -44 || $verify_rt == -45) {
        if ($verify_rt == -44) {
            $json_array = array(
                'errorcode' => -44,
                'message' => 'Please input verification code.'
            );
        } else {
            $json_array = array(
                'errorcode' => -45,
                'message' => 'reCaptcha token required'
            );
        }

        echo json_encode($json_array);
        exit ();
    } else {
        $captcha = $dataArray['token'];

        $secretKey = "6LfP9acUAAAAAMjZzhFgX4f0vZi9_n0Dxdsu8nn5";
        $ip = $_SERVER['REMOTE_ADDR'];

        // post request to server
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'secret' => $secretKey,
                'response' => $captcha,
                'remoteip' => $ip
            ],

            CURLOPT_ENCODING => '',
            CURLOPT_RETURNTRANSFER => true
        ]);

        $output = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($output, true);

        if (!$json['success']) {
            $json_array = array(
                'errorcode' => -43,
                'message' => 'recaptcha verify failed!'
            );
            echo json_encode($json_array);
            exit();
        }
        unset($dataArray['token']);

        $yzm = strtolower($dataArray['yzm']);
        if ($yzm == strtolower($_SESSION['yzm'])) {
            unset($dataArray['yzm']);
        } else {
            $json_array = array(
                'errorcode' => -43,
                'message' => 'Verification code error.'
            );
            echo json_encode($json_array);
            exit ();
        }
    }

    if ($verify_rt == '-3') {
        $json_array = array(
            'errorcode' => -3,
            'message' => 'Mandatory fields cannot be empty.'
        );
        echo json_encode($json_array);
        exit ();
    } elseif ($verify_rt == '-4') {
        $json_array = array(
            'errorcode' => -4,
            // 'message' => '內容格式有誤'
            'message' => 'Input format error.'
        );
        echo json_encode($json_array);
        exit ();
    } elseif ($verify_rt == '-5') {
        $json_array = array(
            'errorcode' => -5,
            'message' => 'Name format error.'
        );
        echo json_encode($json_array);
        exit ();
    }

    // Sector set
    if (!in_array(intval($dataArray['sector']), array(0, 1, 2))) {
        $dataArray['sector'] = 0;
    }

    $fzz = new Fzz_dbc();

    // session time interval check
    /*
    $ip = $_SERVER['REMOTE_ADDR'];
    $sip = md5($ip);
    if (!isset($_SESSION[$sip])) {
        $_SESSION[$sip] = time();
    } else if (time() - $_SESSION[$sip] <= POST_INTERVAL) {
        $remain = POST_INTERVAL - (time() - $_SESSION[$sip]);
        $json_array = array(
            'errorcode' => -999,
            'message' => "您的操作過於頻繁，請 $remain 秒後再試",
        );
        echo json_encode($json_array);
        exit ();
    } else {
        $_SESSION[$sip] = time();
    }
    */

    // 插入数据库
    $dataArray ['dtime'] = time();
    $dataArray ['user_agent'] = substr(trim($_SERVER ['HTTP_USER_AGENT']), 0, 255);

    if (empty($dataArray['user_agent']) || strpos($dataArray['user_agent'], 'Apache-HttpClient') === 0) {
        $json_array = array(
            'errorcode' => -3,
            'message' => 'Access error.'
        );
        echo json_encode($json_array);
        exit();
    }

    /*
    if (empty($dataArray['tel'])) {
        $banned_agents = array(
            'UCWEB7.0.2.37/28/999',
            'Mozilla/4.0 (compatible; MSIE',
            'Mozilla/5.0 (compatible; MSIE',
            'Mozilla/5.0 (hp-tablet;',
            'Mozilla/5.0 (BlackBerry;',
            'Mozilla/5.0 (iPod; U;',
            'Mozilla/5.0 (Linux; U;',
            'Mozilla/5.0 (iPad; U;',
            'Mozilla/5.0 (iPhone; U;',
            'Mozilla/5.0 (Windows; U;',
            'Opera/9.80 (Windows NT 6.1; U;',
            'Mozilla/5.0 (SymbianOS/9.4;',
            'Mozilla/5.0 (Macintosh;',

            'Opera/9.80 (Android 2.3.4;',
            'Opera/9.80 (Macintosh;',
            'Mozilla/5.0 (Windows NT 6.1;'
        );

        foreach ($banned_agents as $item) {
            if (strpos($dataArray['user_agent'], $item) !== false) {
                $json_array = array(
                    'errorcode' => 0,
                    'message' => 'Signature success!'
                );
                echo json_encode($json_array);
                exit();
            }
        }
    }
    */

    $dataArray['ip'] = $_SERVER['REMOTE_ADDR'];

    // time limit use mysql
    $last_time = $fzz->getIpLastTime($dataArray['ip'], $dataArray ['user_agent']);

    if (!empty($last_time)) {
        $lasttime = $last_time['dtime'];
        if (($dataArray ['dtime'] - $lasttime) <= POST_INTERVAL) {
            $json_array = array(
                'errorcode' => -999,
                'message' => "Please don't resubmit.",
            );
            echo json_encode($json_array);
            exit();
        }
    }

    if (!$fzz->InsertFzz($dataArray)) {
        $json_array = array(
            'errorcode' => -10,
            // 'message' => '該身份證已登記'
            'message' => '服務器維護中'
        );
        echo json_encode($json_array);
        exit ();
    }

    $json_array = array(
        'errorcode' => 0,
        'message' => 'Signature success!'
    );
    echo json_encode($json_array);
    exit ();
}