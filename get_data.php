<?php
header("Content-Type:application/json; charset=utf-8");

/*
    $time = strtotime('2017-09-05 18:00:00');
    echo json_encode(array(
        'end_date' => explode('-', date('Y-n-j', $time)),
        'end_time' => explode(':', date('G:i:s', $time)),
        'total' => 80623,
        'sector_1' => 1729,
        'sector_2' => 8808,
        'sector_0' => 70086
    ));
*/

date_default_timezone_set('Asia/Hong_Kong');
include 'Model/Fzz_dbc.php';
$fzz = new Fzz_dbc();

$now = time();

$total = 0;
$sectors = array();


$sectors = $fzz->getCountBySector($now);

foreach ($sectors as $val) {
    $total += $val;
}

echo json_encode(array_merge(
    array(
        'end_date' => explode('-', date('Y-n-j', $now)),
        'end_time' => explode(':', date('G:i:s', $now)),
        'total' => $total
    )
));
