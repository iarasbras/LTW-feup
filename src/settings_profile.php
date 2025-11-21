<?php
    // Start the session
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit();
    }
    


    $username = $_SESSION['username'];

    // Establish connection to database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("DB Connection failed: " . $e->getMessage());
    }

    // Fetch current user data
    $stmt = $db->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    $message = "";
    $errors = [];

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $profile_img_url = $user['profile_img_url']; // default to existing image

        // File upload handling
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../populate/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $tmpName = $_FILES['profile_image']['tmp_name'];
            $originalName = basename($_FILES['profile_image']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($ext, $allowed)) {
                $errors[] = "Only JPG, PNG, or GIF images allowed.";
            } else {
                $newFileName = uniqid('profile_', true) . '.' . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $destination)) {
                    $profile_img_url = $destination;
                } else {
                    $errors[] = "Failed to upload image.";
                }
            }
        }

        // Validate name and email
        if (empty($name)) {
            $errors[] = "Name cannot be empty.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "A valid email address is required.";
        }

        // Update only if no errors
        if (empty($errors)) {
            try {
                $stmt = $db->prepare("UPDATE user SET name = ?, bio = ?, profile_img_url = ?, email = ? WHERE username = ?");
                $stmt->execute([$name, $bio, $profile_img_url, $email, $username]);

                $message = "Profile updated successfully.";

                // Refresh user data
                $stmt = $db->prepare("SELECT * FROM user WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $errors[] = "Update failed: " . $e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title >Profile Settings - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css" />
</head>
<body>

    <?php include 'header.php'; ?>

    <h1 style="text-align:center;">Profile Settings</h1>

    <?php if (!empty($errors)): ?>
        <div class="message error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="settings_profile.php">
        <div class="form-group-settings">
            <label for="profile_image">Profile Image</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*">
            <?php if ($user['profile_img_url']): ?>
                <img src="<?php echo htmlspecialchars($user['profile_img_url']); ?>" alt="Profile Image" style="max-width: 150px; display: block; margin-top: 10px;">
            <?php endif; ?>

            <div class="input-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required />
            </div>

            
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />
            </div>

            <div class="input-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio']); ?></textarea>  
        
            </div>
            

            <div class="button-group">
                <button type="button" class="cancel-button" onclick="window.history.back();">Cancel</button>
                <button type="submit">Save Changes</button>
            </div>
        </div>

 

        
    </form>

    <?php include 'footer.php'; ?>

</body>
</html>


