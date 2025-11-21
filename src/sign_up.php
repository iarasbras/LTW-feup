<?php
    // Establish connection to database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        die();
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm-password'];
        $host_state_raw = $_POST['host_state'];
        
        $host_state = ($host_state_raw === 'Host') ? 'true' : 'false';
        $is_host = ($host_state_raw === 'Host') ? 1 : 0;

        // Check if passwords match
        if ($password !== $confirmPassword) {
            echo "Passwords do not match.";
            die();
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user data into the database
        $stmt = $db->prepare("INSERT INTO user (username, name, password, email, host_state, is_host) VALUES (:username, :name, :password, :email, :host_state, :is_host)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':host_state', $host_state);
        $stmt->bindParam(':is_host', $is_host);

        // Execute the statement
        if ($stmt->execute()) {
            header("Location:login.php");
            echo "Registration successful!";
            
        } else {
            echo "Error occurred while registering." ;
        }

    }
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css"> 
</head>
<body>
        

    <main>
        <div id="register_div">
            <h1>CozyStays</h1>
        <section id="register-form">
            <h2>Sign Up</h2>
            <form action="sign_up.php" method="post">
    
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>
                <div class="input-group">
                    <label for="host_state">Role</label>
                        <select class="custom-select" id="host_state" name="host_state" required>
                            <option value="" disabled selected>Choose...</option>
                            <option value="Host">Host</option>
                            <option value="Guest">Guest</option>
                        </select>
                </div>
                <div class="input-group">
                    <button class="classic-button" type="submit">Register</button>
                </div>
            </form>
            <div class="input-group">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </section>
    </div>
    </main>

</body>
</html>