<?php
    session_start();

    // Check if the user is logged in
    $isLoggedIn = isset($_SESSION['username']);
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

    // Establish connection to database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        die();
    }

    // Check if the request method is DELETE
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // Parse the query string to get the item ID
            $queries = array();
            parse_str($_SERVER['QUERY_STRING'], $queries);
            $userId = $queries['id'];
        
        // delete user from database
        $stmt = $db->prepare('DELETE FROM user WHERE username = ?');
        $stmt->execute([$userId]);

        // delete user ad
        $stmt = $db->prepare('DELETE FROM ad WHERE seller = ?');
        $stmt->execute([$userId]);

        // delete user comments
        //... no


        echo json_encode(['success' => true]);
    }