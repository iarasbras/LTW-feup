<?php
//session starrt
    session_start();

    // Establish connection to database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Redirect if not logged in
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }

    $loggedInUsername = $_SESSION['username'];

    // Fetch user data
    $stmt = $db->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$loggedInUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    // Handle delete listing request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ad_id'])) {
        $adIdToDelete = intval($_POST['delete_ad_id']);
        $checkStmt = $db->prepare("SELECT * FROM ad WHERE ad_id = ? AND seller = ?");
        $checkStmt->execute([$adIdToDelete, $loggedInUsername]);
        $adToDelete = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($adToDelete) {
            $deleteStmt = $db->prepare("DELETE FROM ad WHERE ad_id = ?");
            $deleteStmt->execute([$adIdToDelete]);
            $deleteMessage = "Listing deleted successfully.";
        } else {
            $deleteError = "You don't have permission to delete this listing or it doesn't exist.";
        }
    }

    // Fetch ads for this host
    $stmt = $db->prepare("SELECT * FROM ad WHERE seller = ? ORDER BY ad_id DESC");
    $stmt->execute([$loggedInUsername]);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Host Profile - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css" />

</head>
<body>

    <?php include 'header.php'; ?>

    <div class="profile-container">

        <div class="user-info-section">
            <button class="edit-profile-button" onclick="window.location.href='settings_profile.php?username=<?php echo htmlspecialchars($user['username']); ?>'">&#9881;</button>

            <div class="user-info-header">
                <img src="<?php echo htmlspecialchars($user['profile_img_url'] ?: '../populate/default_profile.jpg'); ?>" alt="Profile Image">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p>@<?php echo htmlspecialchars($user['username']); ?></p>
            </div>

            <div class="user-bio">
                <h3>About me:</h3>
                <p><?php echo htmlspecialchars($user['bio'] ?: 'No bio yet.'); ?></p>
            </div>
        </div>

        <div class="listings-section">
            <h2>Your Listings</h2>

            <?php if (!empty($deleteMessage)): ?>
                <div class="message success"><?php echo htmlspecialchars($deleteMessage); ?></div>
            <?php elseif (!empty($deleteError)): ?>
                <div class="message error"><?php echo htmlspecialchars($deleteError); ?></div>
            <?php endif; ?>

            <?php if (empty($listings)): ?>
                <p>You have no listings yet.</p>
            <?php else: ?>
                <div class="listing-card-container">
                    <?php foreach ($listings as $listing): ?>
                        <div class="listing-card">
                            <img src="<?php echo htmlspecialchars($listing['image_url']); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                            <div class="listing-card-content">
                                <h3>
                                    <a href="ad_page.php?ad_id=<?php echo (int)$listing['ad_id']; ?>">
                                        <?php echo htmlspecialchars($listing['title']); ?>
                                    </a>
                                </h3>

                                <p><?php echo htmlspecialchars($listing['small_desc']); ?></p>
                                <p><strong>Price: </strong><?php echo number_format($listing['price'], 2); ?>€</p>

                                <form method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                    <input type="hidden" name="delete_ad_id" value="<?php echo (int)$listing['ad_id']; ?>">
                                    <button type="submit">Delete Listing</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
