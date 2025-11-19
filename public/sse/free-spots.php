<?php
require_once __DIR__ . '/../../vendor/autoload.php';

// Provedení SSE je nevhodné pro případ > stovky subscriberů
use App\Core\Database;

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

ignore_user_abort(true);

$eventId = (int)($_GET['event_id'] ?? 0);
if ($eventId <= 0) {
    echo "data: 0\n\n";
    ob_flush();
    flush();
    exit;
}

$pdo = Database::getInstance();

while (true) {
    $stmt = $pdo->prepare("
        SELECT e.capacity - COUNT(r.id) AS free
        FROM events e
        LEFT JOIN registrations r ON r.event_id = e.id
        WHERE e.id = ?
    ");
    $stmt->execute([$eventId]);
    $free = (int)$stmt->fetchColumn();

    echo "data: $free\n\n";
    ob_flush();
    flush();

    if (connection_aborted()) {
        break;
    }

    sleep(3);
}

