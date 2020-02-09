<?php

$data = json_encode([
    // 'success' => true,
    'name' => 'Wahab Qureshi',
    'address' => [
        'first',
        'jug' => 15,
        'third',
    ]
]);
//     'data' => [
//         'count' => 1,
//         'users' => [
//             [
//                 'name' => 'Sabhat Qureshi',
//                 'dob' => '01-04-1985',
//                 'smoker' => false,
//                 'hobbies' => [
//                     'swimming',
//                     'pool',
//                     'gaming',
//                 ]
//             ],
//             [
//                 'name' => 'Wahab Qureshi',
//                 'dob' => '10-05-1989',
//                 'smoker' => false,
//                 'hobbies' => [
//                     'swimming',
//                 ]
//             ],
//             [
//                 'name' => 'Jawad Qureshi',
//                 'dob' => '15-09-1996',
//                 'smoker' => false,
//                 'hobbies' => [
//                     'swimming',
//                     'pool',
//                     'somethingelse',
//                     'another one',
//                 ]
//             ],
//         ]
//     ]
// ]);

header('content-type: application/json');
echo $data;
exit;