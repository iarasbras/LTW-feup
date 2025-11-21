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
    <title>Contact Us - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <div id="contact">
            <h1> Contact Us</h1>
            <div class="contact-details">
                <p>We're Here to Help!<br>

                Do you have questions, suggestions or just want to say hello? Our team at CozyStays is always happy to hear from you.</p>

                <p><strong>Email:</strong> helpdesk@fe.up.pt<br>
                <strong>Address:</strong> Rua Dr. Roberto Frias, 4200-465 PORTO</p>

                <p><strong>CozyStays Team:</strong><br>Iara Brás: 202208825</p>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>