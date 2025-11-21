<?php
    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }
    $username = $_SESSION['username'];

    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("DB Error: " . $e->getMessage());
    }

    // Fetch all conversations where user is guest or seller
    $stmt = $db->prepare("
        SELECT c.conversation_id, c.ad_id, c.guest, c.seller, a.title
        FROM conversations c
        JOIN ad a ON c.ad_id = a.ad_id
        WHERE c.guest = :user OR c.seller = :user
        ORDER BY c.conversation_id DESC
    ");
    
    $stmt->execute([':user' => $username]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inbox - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css" />
</head>
<body>

    <?php include 'header.php'; ?>

    <h1>Inbox</h1>

    <?php if (empty($conversations)): ?>
        <p style="text-align: center">No conversations yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($conversations as $c): 
                $otherUser = $c['guest'] === $username ? $c['seller'] : $c['guest'];
            ?>
                <li>
                    <a href="messaging.php?id=<?php echo $c['conversation_id']; ?>">
                        Chat about "<?php echo htmlspecialchars($c['title']); ?>" with <?php echo htmlspecialchars($otherUser); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php include 'footer.php'; ?>

</body>
</html>

