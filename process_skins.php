<?php
header("Content-Type: application/json");

// Gelen JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);

// Gerekli alanların kontrolü
if (!$data || !isset($data['desenId'], $data['desenAdi'], $data['desenLink'], $data['glow'])) {
    http_response_code(400);
    echo json_encode(["error" => "Eksik veya geçersiz veri gönderildi."]);
    exit;
}

// Gelen verileri değişkenlere ata
$desenId = $data['desenId'];
$desenAdi = $data['desenAdi'];
$desenLink = $data['desenLink'];
$glowColors = $data['glow']; // Glow renklerini al

// 'nonbuyable' alanını kontrol et, yoksa varsayılan olarak true kabul et
$nonbuyable = isset($data['nonbuyable']) ? filter_var($data['nonbuyable'], FILTER_VALIDATE_BOOLEAN) : true;

// Gelen veriler arasında 'prime' var mı kontrol et, yoksa varsayılan değeri ata
$prime = isset($data['prime']) && !empty($data['prime']) ? $data['prime'] : "c__yllw";

// Gelen veriler arasında 'price' var mı kontrol et, yoksa varsayılan değeri ata
$price = isset($data['price']) && is_numeric($data['price']) ? $data['price'] : 0;

$filePath = "./skin_007.php";

// Mevcut dosya içeriğini oku
$phpContent = file_get_contents($filePath);

if (!$phpContent) {
    echo json_encode(["error" => "PHP dosyası okunamadı."]);
    exit;
}

// Sabit koordinatları kullanarak 8 adet girdi oluştur
$coordinates = [
    ["x" => 0, "y" => 0],
    ["x" => 174, "y" => 0],
    ["x" => 348, "y" => 0],
    ["x" => 522, "y" => 0],
    ["x" => 696, "y" => 0],
    ["x" => 870, "y" => 0],
    ["x" => 1044, "y" => 0],
    ["x" => 1218, "y" => 0],
    ["x" => 1396, "y" => 0],
];

$regionDictEntries = [];
foreach ($coordinates as $index => $coord) {
    $regionDictEntries[] = <<<EOD
    "skin_{$desenAdi}_{$index}": {
        "texture": "skin_{$desenAdi}",
        "h": 170,
        "w": 174,
        "x": {$coord['x']},
        "y": {$coord['y']}
    }
EOD;
}

$regionDictEntriesString = implode(",\n", $regionDictEntries);

// Yeni içerikleri oluştur
$regionDictEntry = <<<EOD
$regionDictEntriesString,
EOD;

$textureDictEntry = <<<EOD
    "skin_{$desenAdi}": {
        "custom": true,
        "relativePath": "{$desenLink}"
    },
EOD;

// Glow alanını seçilen renk kodlarıyla doldur
$glowEntries = array_map(fn($color) => "\"$color\"", $glowColors);
$glowEntriesString = implode(",\n            ", $glowEntries);

$skinArrayDictEntry = <<<EOD
    {
        "id": {$desenId},
        "guest": false,
        "nonbuyable": {$nonbuyable},
        "price": {$price},
        "priceBefore": 0,
        "prime": "{$prime}",
        "base": [
            "skin_{$desenAdi}_8",
            "skin_{$desenAdi}_7",
            "skin_{$desenAdi}_6",
            "skin_{$desenAdi}_5",
            "skin_{$desenAdi}_4",
            "skin_{$desenAdi}_3",
            "skin_{$desenAdi}_2",
            "skin_{$desenAdi}_1",
            "skin_{$desenAdi}_0"
        ],
        "glow": [
            {$glowEntriesString}
        ]
    },
EOD;

// 'nonbuyable' değerini JSON encode ile düzelt
$skinArrayDictEntry = str_replace('"nonbuyable": true', '"nonbuyable": true', $skinArrayDictEntry);
$skinArrayDictEntry = str_replace('"nonbuyable": false', '"nonbuyable": false', $skinArrayDictEntry);

// Bloklara ekleme işlemi
$phpContent = preg_replace(
    '/"regionDict": \{/',
    "\"regionDict\": {\n$regionDictEntry",
    $phpContent
);

$phpContent = preg_replace(
    '/"textureDict": \{/',
    "\"textureDict\": {\n$textureDictEntry",
    $phpContent
);

$phpContent = preg_replace(
    '/"skinArrayDict": \[/',
    "\"skinArrayDict\": [\n$skinArrayDictEntry",
    $phpContent
);

// Güncellenmiş içeriği dosyaya yaz
$result = file_put_contents($filePath, $phpContent);

if ($result === false) {
    echo json_encode(["error" => "Dosya yazılamadı."]);
} else {
    echo json_encode(["success" => "Veriler başarıyla işlendi ve PHP dosyasına yazıldı."]);
}
?>
