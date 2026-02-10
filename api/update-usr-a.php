<?php
header("Content-Type: application/json; charset=utf-8");

$file = __DIR__ . "/usr-a.json";

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["Users"])) {
    echo json_encode([
        "success" => false,
        "msg" => "بيانات غير صحيحة"
    ]);
    exit;
}

$data = [
    "success" => true,
    "Users" => $input["Users"]
];

file_put_contents(
    $file,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode([
    "success" => true,
    "msg" => "تم التحديث بنجاح"
]);
