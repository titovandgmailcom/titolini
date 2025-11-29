<?php
/**
 * ═══════════════════════════════════════════════════════════
 * КОНФИГУРАЦИЯ БАЗЫ ДАННЫХ
 * Интернет-магазин "Райский уголок"
 * ═══════════════════════════════════════════════════════════
 */

// Параметры подключения к базе данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'cz01249_2121');
define('DB_USER', 'cz01249_2121');
define('DB_PASS', '100680');
define('DB_CHARSET', 'utf8mb4');

// Создание PDO подключения
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // Логирование ошибки (в production использовать файл логов)
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Показ пользовательской ошибки
    die("
        <!DOCTYPE html>
        <html lang='ru'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Ошибка подключения</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    background: linear-gradient(135deg, #F0F8EE 0%, #E8F5E9 100%);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    margin: 0;
                }
                .error-box {
                    background: white;
                    padding: 40px;
                    border-radius: 16px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                    text-align: center;
                    max-width: 500px;
                }
                .error-box h1 {
                    color: #E31E24;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .error-box p {
                    color: #666;
                    line-height: 1.6;
                }
                .error-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <div class='error-icon'>⚠️</div>
                <h1>Ошибка подключения к базе данных</h1>
                <p>Извините, в данный момент сервис недоступен. Пожалуйста, попробуйте позже.</p>
                <p><small>Код ошибки: DB_CONNECTION_FAILED</small></p>
            </div>
        </body>
        </html>
    ");
}

/**
 * Функция безопасного выполнения запросов
 * 
 * @param string $query SQL запрос с плейсхолдерами
 * @param array $params Параметры для prepared statement
 * @return PDOStatement
 */
function db_query($query, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage() . " | Query: " . $query);
        throw $e;
    }
}

/**
 * Получить одну запись
 */
function db_fetch($query, $params = []) {
    return db_query($query, $params)->fetch();
}

/**
 * Получить все записи
 */
function db_fetch_all($query, $params = []) {
    return db_query($query, $params)->fetchAll();
}

/**
 * Получить количество строк
 */
function db_count($query, $params = []) {
    return db_query($query, $params)->rowCount();
}

/**
 * Получить ID последней вставленной записи
 */
function db_last_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

/**
 * Начать транзакцию
 */
function db_begin_transaction() {
    global $pdo;
    return $pdo->beginTransaction();
}

/**
 * Подтвердить транзакцию
 */
function db_commit() {
    global $pdo;
    return $pdo->commit();
}

/**
 * Откатить транзакцию
 */
function db_rollback() {
    global $pdo;
    return $pdo->rollBack();
}