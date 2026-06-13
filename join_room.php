<?php
require_once 'config.php';

$room_code = $_POST['room_code'] ?? '';

if (empty($room_code)) {
    echo json_encode(['success' => false, 'message' => 'Code de salle manquant']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_code = ?");
$stmt->execute([$room_code]);
$room = $stmt->fetch();

if (!$room) {
    echo json_encode(['success' => false, 'message' => 'Salle introuvable']);
    exit;
}

if ($room['player_north_active']) {
    echo json_encode(['success' => false, 'message' => 'Salle déjà complète']);
    exit;
}

$stmt = $pdo->prepare("UPDATE rooms SET player_north_active = TRUE WHERE room_code = ?");
$stmt->execute([$room_code]);

echo json_encode(['success' => true, 'room_code' => $room_code, 'player_role' => 'north']);
?>
