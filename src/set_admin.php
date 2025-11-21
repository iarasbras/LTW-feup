<?php
    session_start();

    // Check if the user is logged in
    $isLoggedIn = isset($_SESSION['username']);
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

    // Establish connection to SQLite database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        die();
    }

    if (!$isAdmin) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to perform this action']);
        return;
    }

    // Check if the request method is DELETE
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input_data = file_get_contents('php://input');
        $data = json_decode($input_data, true);
        $stmt = $db->prepare('UPDATE user SET is_admin = ? WHERE username = ?');
        $stmt->execute([$data['is_admin'], $data['username']]);
        echo json_encode(['success' => true]);
        return;
    }