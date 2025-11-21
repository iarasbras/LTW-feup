<?php
    // Start the session
    session_start();
    // Check if the user is logged in
    $isLoggedIn = isset($_SESSION['username']);

    // Check if the user is already logged in
    if (isset($_SESSION['username'])) {
        header('Location: main_page.php');
        exit;
    }
    // Establish connection to SQLite database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        die();
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Retrieve form data
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Fetch user data from the database
        $stmt = $db->prepare("SELECT * FROM user WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the password
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['is_host'] = $user['is_host'];

            // Redirect to main page or any other page after successful login
            header('Location: main_page.php');
            exit;
        } else {
            // Invalid username or password
            echo "Invalid username or password.";
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <link rel="stylesheet" href="stylesheet.css">
    <title>Login - CozyStays</title>
  </head>
  <body>
    <div id="login_div">
        <h1>CozyStays</h1>
        <h2>Log in or Register</h2>
        <form action="login.php" method="get">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required> 
            </div>  
            <div class="input-group">
                <label for="password">Password</label> 
                <input type="password" id="password" name="password" required > 
            </div>    
            <div class="input-group">
              <button class="classic-button" formaction="login.php" formmethod="post" type="submit">Login</button>
            </div>
        </form>
      
        <div class="input-group">
            <p>Don't have an account? <a href="sign_up.php" >Sign up</a></p>
        </div>
    </div>
    
  </body>
</html>


