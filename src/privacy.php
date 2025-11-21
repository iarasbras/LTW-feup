<?php
// Start the session
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <div id="privacy-section">
            <h1>Privacy Policy</h1>

            <h2>1. Information we collect</h2>
            <p>At CozyStays, we collect personal information such as name and email address when you create an account or make a purchase.</p>

            <h2>2. How we use your information</h2>
            <p>We use your information to process transactions, improve our services, and communicate with you about your account and our products.</p>

            <h2>3. Data Security</h2>
            <p>We take data security seriously and implement measures to protect your information from unauthorized access, alteration, disclosure or destruction.</p>

            <h2>4. Changes to Privacy Policy</h2>
            <p>We may update our privacy policy from time to time. Any changes will be reflected on this page, and we encourage you to review our policy periodically.</p>

            <h2>5. Contact Us</h2>
            <p>If you have any questions or concerns about our privacy policy, please <a href="contact_us.php" >contact us.</a></p>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>