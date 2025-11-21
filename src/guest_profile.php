<?php
    // Start the session
    session_start();
  // Establish connection to SQLite database
      try {
          $db = new PDO('sqlite:../db/cozystays.db');
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
          echo 'Connection failed: ' . $e->getMessage();
          die();
      }

    // Redirect if not logged in
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }

    // Check if the user is logged in
    $loggedInUsername = $_SESSION['username'];

    // Fetch user data
    $stmt = $db->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$loggedInUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch bookings
    $stmt = $db->prepare("
        SELECT b.booking_id, b.booked_at, b.until_at, b.state, 
              a.ad_id, a.title, a.description, a.image_url, a.small_desc
        FROM booking b
        JOIN ad a ON b.ad_id = a.ad_id
        WHERE b.guest = ? AND b.state = 'Confirmed'
        ORDER BY b.booked_at DESC
    ");
    $stmt->execute([$loggedInUsername]);
    $previousBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guest Profile - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="profile-container">

        <div class="user-info-section">
            <button class="edit-profile-button" onclick="window.location.href='settings_profile.php?username=<?php echo htmlspecialchars($user['username']); ?>'">&#9881;</button>

            <div class="user-info-header">
                <img src="<?php echo htmlspecialchars($user['profile_img_url'] ?: '../populate/default_profile.jpg'); ?>" alt="Profile Image" style="width:100%; border-radius: 10px;">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p>@<?php echo htmlspecialchars($user['username']); ?></p>
            </div>

            <div class="user-bio">
                <h3>About me:</h3>
                <p><?php echo htmlspecialchars($user['bio'] ?: 'No bio yet.'); ?></p>
            </div>
        </div>

        <div class="bookings-section">
            <h2>Previous Bookings</h2>

            <?php if (empty($previousBookings)): ?>
                <p>You have no completed bookings yet.</p>
            <?php else: ?>
                <div class="booking-card-container">
                    <?php foreach ($previousBookings as $booking): ?>
                        <div class="booking-card">
                            <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking['title']); ?>">
                            <div class="booking-card-content">
                                <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                                <p><?php echo htmlspecialchars($booking['small_desc']); ?></p>
                                <p><strong>Booked from:</strong> <?php echo htmlspecialchars($booking['booked_at']); ?></p>
                                <p><strong>Until:</strong> <?php echo htmlspecialchars($booking['until_at']); ?></p>
                                <a href="review.php?ad_id=<?php echo htmlspecialchars($booking['ad_id']); ?>" class="rate-button">Rate</a>
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
