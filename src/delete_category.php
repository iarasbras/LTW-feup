<?php
    session_start();

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        echo json_encode(['success' => false]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $categoryId = $data['id'] ?? null;

    if (!$categoryId) {
        echo json_encode(['success' => false]);
        exit;
    }

    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $stmt = $db->prepare("DELETE FROM category WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
