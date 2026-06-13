<?php
require_once 'config.php';

function generateRoomCode($length = 6) {
    return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $length));
}

$room_code = generateRoomCode();
$initial_board = json_encode(array_fill(0, 14, 4));

$stmt = $pdo->prepare("INSERT INTO rooms (room_code, board, player_south_active) VALUES (?, ?, ?)");
$stmt->execute([$room_code, $initial_board, true]);

echo json_encode(['success' => true, 'room_code' => $room_code, 'player_role' => 'south']);
?>
