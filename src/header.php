<?php
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Define variables
    $isLoggedIn = isset($_SESSION['username']);
    $username = $_SESSION['username'] ?? '';
    $isAdmin = !empty($_SESSION['is_admin']);
    $isHost = !empty($_SESSION['is_host']);
    $currentPage = basename($_SERVER['PHP_SELF']);

?>

<header>
    <nav>
        <h1><a href="main_page.php">-`♡´-  Cozystays</a></h1>

        <h2> <h2>

        <!-- Login or Logout -->
        <a href="<?php echo $isLoggedIn ? 'logout.php' : 'login.php'; ?>">
            <?php echo $isLoggedIn ? 'Logout' : 'Login'; ?>
        </a>

        <?php if ($isLoggedIn): ?>
            <?php if ($currentPage !== 'inbox.php'): ?>
                <a href="inbox.php">Inbox</a>
            <?php endif; ?>

            <!-- if it's host show upload_ad button -->
            <?php if ($isHost && $currentPage !== 'upload_ad.php'): ?>
                <a href="upload_ad.php">Post</a>
            <?php endif; ?>

            <!-- select either guest or host profile -->
            <?php 
                $profilePage = $isHost ? 'host_profile.php' : 'guest_profile.php';
                if ($currentPage !== $profilePage):
            ?>
                <a href="<?php echo $profilePage; ?>">Profile</a>
            <?php endif; ?>

            <!-- see if it's an admin -->
            <?php if ($isAdmin && $currentPage !== 'admin_page.php'): ?>
                <a href="admin_page.php">Admin</a>
            <?php endif; ?>

        <?php endif; ?>
    </nav>
</header>

