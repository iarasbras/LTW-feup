<?php
    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');

    if (!$name) {
        echo json_encode(['success' => false, 'error' => 'Name is required']);
        exit;
    }

    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $stmt = $db->prepare("INSERT INTO category (categ_name) VALUES (?)");
        $stmt->execute([$name]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
