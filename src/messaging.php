<?php
    // Start the session
    session_start();

    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }
    $username = $_SESSION['username'];

    if (!isset($_GET['id'])) {
        die("Conversation not specified.");
    }
    $conversation_id = (int)$_GET['id'];

    // Establish connection to database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("DB Error: " . $e->getMessage());
    }

    // Fetch conversation
    $stmt = $db->prepare("SELECT * FROM conversations WHERE conversation_id = ?");
    $stmt->execute([$conversation_id]);
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$conv) die("Conversation not found.");

    if ($conv['guest'] !== $username && $conv['seller'] !== $username) {
        die("Unauthorized access.");
    }

    // Fetch ad info
    $adStmt = $db->prepare("SELECT title FROM ad WHERE ad_id = ?");
    $adStmt->execute([$conv['ad_id']]);
    $ad = $adStmt->fetch(PDO::FETCH_ASSOC);

    // Handle message post
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
        $msg = trim($_POST['message']);
        if ($msg !== '') {
            $send = $db->prepare("INSERT INTO messages (conversation_id, sender, message) VALUES (?, ?, ?)");
            $send->execute([$conversation_id, $username, $msg]);
            header("Location: messaging.php?id=$conversation_id");
            exit;
        }
    }

    // Fetch messages
    $msgStmt = $db->prepare("SELECT * FROM messages WHERE conversation_id = ? ORDER BY sent_at ASC");
    $msgStmt->execute([$conversation_id]);
    $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Messaging - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css" />
</head>
<body>

    <?php include 'header.php'; ?>

    <h2>Chat about "<?php echo htmlspecialchars($ad['title']); ?>"</h2>

    <div class="chat-container">
        <?php foreach ($messages as $m): ?>
            <div class="msg <?php echo $m['sender'] === $username ? 'own' : 'other'; ?>">
                <div class="sender"><?php echo htmlspecialchars($m['sender']); ?>:</div>
                <div><?php echo nl2br(htmlspecialchars($m['message'])); ?></div>
                <div><?php echo $m['sent_at']; ?></div>
            </div>
        <?php endforeach; ?>


    <form method="POST">
        <textarea name="message" rows="4" cols="50" required placeholder="Write a message..."></textarea><br>
        <button type="submit">Send</button>
    </form>

    </div>

    <?php include 'footer.php'; ?>

</body>
</html>

