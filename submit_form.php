<?php
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Allow: POST");
    http_response_code(405);
    echo json_encode(['error' => 'Только POST-запросы разрешены']);
    exit;
}

if (strpos($_SERVER['REQUEST_URI'], '/submit_form') === false) {
    http_response_code(404);
    echo json_encode(['error' => 'Неверный URL обработчика формы']);
    exit;
}

$data = [
    'name' => trim($_POST['name'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'service' => trim($_POST['service'] ?? ''),
    'message' => trim($_POST['message'] ?? ''),
    'created_at' => date('Y-m-d H:i:s')
];
$errors = [];
if (empty($data['name'])) $errors['name'] = 'Введите имя';
if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Введите корректный email';
if (empty($data['phone'])) $errors['phone'] = 'Введите телефон';
if (empty($data['service'])) $errors['service'] = 'Выберите услугу';
if (empty($data['message'])) $errors['message'] = 'Введите сообщение';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}
$config = [
    'host' => 'localhost',
    'dbname' => 'psychologist_db',
    'username' => 'root',
    'password' => ''
];
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO appointments (name, email, phone, service, message, created_at)
        VALUES (:name, :email, :phone, :service, :message, :created_at)
    ");
    $stmt->execute($data);

    echo json_encode([
        'success' => true,
        'message' => 'Заявка успешно отправлена!',
        'data' => $data
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка базы данных: ' . $e->getMessage()
    ]);
}
?>