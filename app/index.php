<?php

$data = json_encode([
    'success' => true,
    'data' => [
        'count' => 1,
        'users' => [
            'name' => 'Wahab Qureshi',
            'dob' => '10-05-1989',
            'smoker' => false,
            'hobbies' => [
                'swimming',
                'pool',
                'gaming',
            ]
        ]
    ]
]);

header('content-type: application/json');
echo $data;
exit;