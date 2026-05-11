<?php
header('Content-Type: application/json');

$env    = parse_ini_file(__DIR__ . '/../.env');
$apiKey = $env['UPLOADTHING_API_KEY'] ?? '';

if (!$apiKey) {
    echo json_encode(['success' => false, 'message' => 'API key not configured']);
    exit;
}

if (empty($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error']);
    exit;
}

if ($file['size'] > 4 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 4MB)']);
    exit;
}

$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mimeType, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Only image files are allowed']);
    exit;
}

// Step 1: Ask UploadThing for an upload URL
$ch = curl_init('https://api.uploadthing.com/v7/prepareUpload');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-uploadthing-api-key: ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'fileName' => $file['name'],
        'fileSize' => $file['size'],
        'fileType' => $mimeType
    ])
]);

$prepare = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($prepare['url'])) {
    echo json_encode(['success' => false, 'message' => 'Could not get upload URL from UploadThing']);
    exit;
}

// Step 2: Upload the file to the URL we got
$ch = curl_init($prepare['url']);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST  => 'PUT',
    CURLOPT_POSTFIELDS     => ['file' => new CURLFile($file['tmp_name'], $mimeType, 'file')]
]);

$uploadResult = json_decode(curl_exec($ch), true);
$httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 && $httpCode !== 201) {
    echo json_encode(['success' => false, 'message' => 'File upload to cloud failed']);
    exit;
}

if (empty($uploadResult['url'])) {
    echo json_encode(['success' => false, 'message' => 'No URL returned after upload']);
    exit;
}

echo json_encode(['success' => true, 'url' => $uploadResult['url']]);
?>
