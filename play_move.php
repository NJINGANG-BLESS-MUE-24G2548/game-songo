<?php
require_once 'config.php';

$room_code = $_POST['room_code'] ?? '';
$index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
$player_role = $_POST['player_role'] ?? '';

if (empty($room_code) || $index === -1 || empty($player_role)) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_code = ?");
$stmt->execute([$room_code]);
$room = $stmt->fetch();

if (!$room) {
    echo json_encode(['success' => false, 'message' => 'Salle introuvable']);
    exit;
}

if ($room['game_over']) {
    echo json_encode(['success' => false, 'message' => 'La partie est terminée']);
    exit;
}

if ($room['current_turn'] !== $player_role) {
    echo json_encode(['success' => false, 'message' => "Ce n'est pas votre tour"]);
    exit;
}

$board = json_decode($room['board']);
$score_north = (int)$room['score_north'];
$score_south = (int)$room['score_south'];

// Vérification camp
if (($player_role === 'south' && ($index < 0 || $index > 6)) || 
    ($player_role === 'north' && ($index < 7 || $index > 13))) {
    echo json_encode(['success' => false, 'message' => "Ce n'est pas votre camp"]);
    exit;
}

if ($board[$index] === 0) {
    echo json_encode(['success' => false, 'message' => "Case vide"]);
    exit;
}

// Logique du Songo
$seeds = $board[$index];
$board[$index] = 0;
$current_idx = $index;
$start_pit = $index;

while ($seeds > 0) {
    $current_idx = ($current_idx + 1) % 14;
    if ($current_idx === $start_pit) {
        $current_idx = ($current_idx + 1) % 14;
    }
    $board[$current_idx]++;
    $seeds--;
}

// Captures
$captures = 0;
$temp_idx = $current_idx;
$is_south = ($player_role === 'south');

while (true) {
    $in_opponent_range = ($is_south && $temp_idx >= 7 && $temp_idx <= 13) || 
                         (!$is_south && $temp_idx >= 0 && $temp_idx <= 6);
    
    if (!$in_opponent_range) break;

    $is_first_pit = ($temp_idx === 7 || $temp_idx === 0);
    
    if ($board[$temp_idx] >= 2 && $board[$temp_idx] <= 4) {
        if ($is_first_pit && $temp_idx === $current_idx) {
            break; 
        }
        $captures += $board[$temp_idx];
        $board[$temp_idx] = 0;
        $temp_idx = ($temp_idx - 1 + 14) % 14;
    } else {
        break;
    }
}

if ($is_south) {
    $score_south += $captures;
} else {
    $score_north += $captures;
}

// Changement de tour
$next_turn = ($player_role === 'south') ? 'north' : 'south';

// Vérification fin de partie
$game_over = false;
$winner = null;
if ($score_south >= 28) {
    $game_over = true;
    $winner = 'south';
} else if ($score_north >= 28) {
    $game_over = true;
    $winner = 'north';
} else {
    $total_seeds = array_sum($board);
    if ($total_seeds < 10) {
        $game_over = true;
        if ($score_south > $score_north) $winner = 'south';
        else if ($score_north > $score_south) $winner = 'north';
        else $winner = 'draw';
    }
}

$last_move = ($player_role === 'south' ? 'Sud' : 'Nord') . " a joué depuis " . ($index < 7 ? "S".($index+1) : "N".($index-6));

$stmt = $pdo->prepare("UPDATE rooms SET board = ?, score_north = ?, score_south = ?, current_turn = ?, game_over = ?, winner = ?, last_move = ? WHERE room_code = ?");
$stmt->execute([json_encode($board), $score_north, $score_south, $next_turn, $game_over, $winner, $last_move, $room_code]);

echo json_encode(['success' => true]);
?>
