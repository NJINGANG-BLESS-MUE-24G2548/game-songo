CREATE DATABASE IF NOT EXISTS songo_db;
USE songo_db;

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_code VARCHAR(10) UNIQUE NOT NULL,
    board TEXT NOT NULL,
    score_north INT DEFAULT 0,
    score_south INT DEFAULT 0,
    current_turn ENUM('south', 'north') DEFAULT 'south',
    player_south_active BOOLEAN DEFAULT FALSE,
    player_north_active BOOLEAN DEFAULT FALSE,
    game_over BOOLEAN DEFAULT FALSE,
    winner VARCHAR(20) DEFAULT NULL,
    last_move VARCHAR(255) DEFAULT 'La partie commence.',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
