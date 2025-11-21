<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ad_id'])) {
    $ad_id = (int)$_POST['ad_id'];

    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("DB Error: " . $e->getMessage());
    }

    // Get ad and seller
    $stmt = $db->prepare("SELECT seller FROM ad WHERE ad_id = ?");
    $stmt->execute([$ad_id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) die("Ad not found.");
    $seller = $ad['seller'];

    if ($seller === $username) {
        die("You can't message yourself.");
    }

    // Check if conversation already exists
    $check = $db->prepare("
        SELECT conversation_id FROM conversations 
        WHERE ad_id = ? AND guest = ? AND seller = ?
    ");
    $check->execute([$ad_id, $username, $seller]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $conversation_id = $existing['conversation_id'];
    } else {
        $insert = $db->prepare("
            INSERT INTO conversations (ad_id, guest, seller) 
            VALUES (?, ?, ?)
        ");
        $insert->execute([$ad_id, $username, $seller]);
        $conversation_id = $db->lastInsertId();
    }

    header("Location: messaging.php?id=$conversation_id");
    exit;
}
?>
