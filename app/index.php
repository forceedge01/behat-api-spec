<?php

$data = null;
if (isset($_GET['exception'])) {
    http_response_code(500);
    $data = json_encode([
        'success' => false,
        'error' => 'Something went wrongsssss'
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(200);
    $data = json_encode([
        'success' => true,
        'id' => 2233
    ]);
} elseif (isset($_GET['test'])) {
    http_response_code(201);
    $data = json_encode([
        'success' => true,
        'msg' => 'Record created',
        'id' => '887987fa89s7df87as9d7f87asdf798asdf'
    ]);
} else {
    http_response_code(200);
    $data = json_encode([
        'success' => true,
        'name' => 'Wahab Qureshi',
        'address' => [
            'first',
            'jug' => '15',
            'third',
        ]
    ]);
}

header('content-type: application/json');
echo $data;
exit;