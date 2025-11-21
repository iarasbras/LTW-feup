<?php
session_start();

// Optional: Require login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
// Check if the user is logged in
$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Establish connection to database
try {
    $db = new PDO('sqlite:../db/cozystays.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die();
}

// Fetch user information if logged in
if ($isLoggedIn) {
    $username = $_SESSION['username'];
    $stmt = $db->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile - CozyStays</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
  <main class="profile-container">
    <section class="profile-card">
      <h2>My Profile</h2>

      <form action="actions/update_profile.php" method="post" class="profile-form">

        <!-- Profile image -->
        <div class="profile-image">
          <img src="assets/img/default-profile.jpg" alt="Profile Picture">
          <span class="edit-icon" title="Change photo">✏️</span>
        </div>

        <!-- Name -->
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="Ana" required>

        <!-- Username -->
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="ana_stays" placeholder="username" required>

        <!-- Email (readonly) -->
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="123@gmail.com" readonly>

        <!-- Password fields -->
        <label for="current-password">Current Password</label>
        <input type="password" id="current-password" name="current_password" placeholder="••••••••">

        <label for="new-password">New Password</label>
        <input type="password" id="new-password" name="new_password" placeholder="New password">

        <label for="confirm-password">Confirm New Password</label>
        <input type="password" id="confirm-password" name="confirm_password" placeholder="Repeat new password">

        <!-- Bio -->
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="4" placeholder="Tell us a bit about yourself...">Passionate about hospitality and making every stay special...</textarea>

        <!-- Buttons -->
        <div class="form-buttons">
          <button type="reset" class="cancel">Cancel</button>
          <button type="submit" class="save">Save Changes</button>
        </div>
      </form>

      <p><a href="index.php">← Back to main page</a></p>
    </section>
  </main>
</body>
</html>

