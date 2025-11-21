<?php
// Start the session
session_start();
$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About us - Cozystays</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <div id="about-section">
            <h1>About us</h1>
            
            
                <h2>What is CozyStays?</h2>
                <p>CozyStays is a community-driven platform for booking unique homes, apartments, and vacation rentals. Whether you're planning a weekend getaway, a family vacation, or listing your space as a host, CozyStays connects people with places that feel like home.</p>
            
            
                <h2>How does booking work?</h2>
                <p>Guests can browse a wide variety of listings, check availability, and book directly through our secure platform. Each listing includes detailed descriptions, photos, and user reviews to help you choose the perfect stay.</p>
            

            
                <h2>How can I become a host?</h2>
                <p>If you have a space you'd like to share, you can sign up as a host, create a listing, and start earning. CozyStays makes it easy to manage your availability, set prices, and connect with guests.</p>
            

            
                <h2>What safety measures are in place?</h2>
                <p>CozyStays is committed to safety and trust. Visit our <a href="privacy.php">Privacy Policy</a> page for more information.</p>
            

            
                <h2>Need help or have questions?</h2>
                <p>Our support team is here for you. Visit our <a href="contact.php">Contact Us</a> page for more ways to reach out.</p>
            
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>

