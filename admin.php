<?php
session_start();

//аутентификация
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'securepassword123') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = 'Неверные учетные данные';
        }
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Вход в админку</title>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Montserrat', sans-serif;
                    background: #121212;
                    color: #e0e0e0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .login-form {
                    background: #1e1e1e;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                    width: 100%;
                    max-width: 400px;
                }
                .login-form h2 {
                    color: #50c878;
                    text-align: center;
                    margin-bottom: 30px;
                }
                .form-group {
                    margin-bottom: 20px;
                }
                .form-group label {
                    display: block;
                    margin-bottom: 8px;
                }
                .form-group input {
                    width: 100%;
                    padding: 12px 15px;
                    background: #2d2d2d;
                    border: 1px solid #333;
                    border-radius: 5px;
                    color: #e0e0e0;
                    font-family: 'Montserrat', sans-serif;
                }
                .form-group input:focus {
                    outline: none;
                    border-color: #50c878;
                }
                button {
                    width: 100%;
                    padding: 12px;
                    background: #50c878;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-family: 'Montserrat', sans-serif;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }
                button:hover {
                    background: #3a9d5d;
                }
                .error {
                    color: #ff6b6b;
                    text-align: center;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="login-form">
                <h2>Вход в админ-панель</h2>
                <?php if (isset($error)): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Логин</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit">Войти</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Подключение к базе данных
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

    //получение заявок
    $stmt = $pdo->query("SELECT * FROM appointments ORDER BY created_at DESC");
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    if (isset($_GET['mark_as_processed']) && is_numeric($_GET['mark_as_processed'])) {
        $id = (int)$_GET['mark_as_processed'];
        $pdo->prepare("UPDATE appointments SET status = 'processed' WHERE id = ?")->execute([$id]);
        header("Location: admin.php");
        exit;
    }

    //делит заявок
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $id = (int)$_GET['delete'];
        $pdo->prepare("DELETE FROM appointments WHERE id = ?")->execute([$id]);
        header("Location: admin.php");
        exit;
    }
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }
        .admin-header {
            background: #1e1e1e;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .admin-header h1 {
            color: #50c878;
            margin: 0;
        }
        .logout-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background: #e05555;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stat-card h3 {
            margin-top: 0;
            color: #50c878;
        }
        .stat-number {
            font-size: 24px;
            font-weight: 700;
        }
        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .appointment-card {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
            border-left: 4px solid #50c878;
        }
        .appointment-card.new {
            border-left-color: #ff6b6b;
        }
        .appointment-card h3 {
            margin-top: 0;
            color: #50c878;
        }
        .appointment-card p {
            margin: 8px 0;
        }
        .appointment-meta {
            display: flex;
            justify-content: space-between;
            color: #a0a0a0;
            font-size: 14px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #333;
        }
        .appointment-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
            flex: 1;
        }
        .process-btn {
            background: #50c878;
            color: white;
        }
        .process-btn:hover {
            background: #3a9d5d;
        }
        .delete-btn {
            background: #ff6b6b;
            color: white;
        }
        .delete-btn:hover {
            background: #e05555;
        }
        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-new {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
        }
        .status-processed {
            background: rgba(80, 200, 120, 0.2);
            color: #50c878;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <h1>Админ-панель</h1>
        <form method="POST" action="logout.php">
            <button type="submit" class="logout-btn">Выйти</button>
        </form>
    </header>
    
    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Всего заявок</h3>
                <div class="stat-number"><?= count($appointments) ?></div>
            </div>
            <div class="stat-card">
                <h3>Новые</h3>
                <div class="stat-number"><?= count(array_filter($appointments, fn($a) => $a['status'] === 'new')) ?></div>
            </div>
            <div class="stat-card">
                <h3>Обработанные</h3>
                <div class="stat-number"><?= count(array_filter($appointments, fn($a) => $a['status'] === 'processed')) ?></div>
            </div>
        </div>
        
        <div class="appointments-grid">
            <?php foreach ($appointments as $appointment): ?>
                <div class="appointment-card <?= $appointment['status'] === 'new' ? 'new' : '' ?>">
                    <span class="status-badge status-<?= $appointment['status'] ?>">
                        <?= $appointment['status'] === 'new' ? 'Новая' : 'Обработана' ?>
                    </span>
                    <h3><?= htmlspecialchars($appointment['name']) ?></h3>
                    <p><strong>Услуга:</strong> <?= htmlspecialchars($appointment['service']) ?></p>
                    <p><strong>Телефон:</strong> <?= htmlspecialchars($appointment['phone']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($appointment['email']) ?></p>
                    <p><strong>Сообщение:</strong> <?= htmlspecialchars($appointment['message']) ?></p>
                    
                    <div class="appointment-meta">
                        <span>Дата: <?= date('d.m.Y H:i', strtotime($appointment['created_at'])) ?></span>
                    </div>
                    
                    <div class="appointment-actions">
                        <?php if ($appointment['status'] === 'new'): ?>
                            <a href="admin.php?mark_as_processed=<?= $appointment['id'] ?>" class="action-btn process-btn">Отметить как обработанную</a>
                        <?php endif; ?>
                        <a href="admin.php?delete=<?= $appointment['id'] ?>" class="action-btn delete-btn">Удалить</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>