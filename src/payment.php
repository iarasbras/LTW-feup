<?php
    // Start the session
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }

    $db = new PDO('sqlite:../db/cozystays.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = $_SESSION['username'];
    $adId = $_GET['ad_id'] ?? null;
    $checkIn = $_GET['check_in'] ?? null;
    $checkOut = $_GET['check_out'] ?? null;

    if (!$adId || !$checkIn || !$checkOut) {
        die("Missing booking information.");
    }

    // Fetch ad info (including price per night)
    $stmt = $db->prepare("SELECT * FROM ad WHERE ad_id = ?");
    $stmt->execute([$adId]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) {
        die("Ad not found.");
    }

    // Calculate number of nights and total price
    $startDate = new DateTime($checkIn);
    $endDate = new DateTime($checkOut);
    $interval = $startDate->diff($endDate);
    $nights = $interval->days;

    if ($nights <= 0) {
        die("Invalid booking dates.");
    }

    $totalPrice = $nights * $ad['price'];

    // Process form submission
    $errors = [];
    $success = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // mock payment validation
        $cardNumber = $_POST['card_number'] ?? '';
        $expiry = $_POST['expiry'] ?? '';
        $cvv = $_POST['cvv'] ?? '';

        if (!preg_match('/^\d{16}$/', $cardNumber)) {
            $errors[] = "Invalid card number. Must be 16 digits.";
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) {
            $errors[] = "Invalid expiry date. Use MM/YY format.";
        }
        if (!preg_match('/^\d{3}$/', $cvv)) {
            $errors[] = "Invalid CVV. Must be 3 digits.";
        }

        if (empty($errors)) {
            // no overlaping bookings
            $stmt = $db->prepare("
                SELECT 1 FROM booking
                WHERE ad_id = ?
                AND state = 'Confirmed'
                AND (
                    (? BETWEEN booked_at AND until_at)
                    OR (? BETWEEN booked_at AND until_at)
                    OR (booked_at BETWEEN ? AND ?)
                    OR (until_at BETWEEN ? AND ?)
                )
            ");
            $stmt->execute([$adId, $checkIn, $checkOut, $checkIn, $checkOut, $checkIn, $checkOut]);

            if ($stmt->fetch()) {
                $errors[] = "Sorry, those dates are already booked.";
            } else {
                // Insert booking as confirmed
                $insert = $db->prepare("
                    INSERT INTO booking (booked_at, until_at, guest, ad_id, state)
                    VALUES (?, ?, ?, ?, 'Confirmed')
                ");
                $insert->execute([$checkIn, $checkOut, $username, $adId]);
                $bookingId = $db->lastInsertId();

                $success = true;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payment - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css" />
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="payment-container">
        <h1>Complete Your Booking</h1>

        <?php if ($success): ?>
            <p><strong>Booking successful!</strong></p>
            <p><a href="guest_profile.php">Check your reservations.</a></p>
        <?php else: ?>

            <?php if ($errors): ?>
                <div class="errors" style="color: red;">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h2>Booking Details</h2>
            <p><strong>Check-in:</strong> <?php echo htmlspecialchars($checkIn); ?></p>
            <p><strong>Check-out:</strong> <?php echo htmlspecialchars($checkOut); ?></p>
            <p><strong>Nights:</strong> <?php echo $nights; ?></p>
            <p><strong>Price per night:</strong> <?php echo number_format($ad['price'], 2); ?>€</p>
            <p><strong>Total price:</strong><?php echo number_format($totalPrice, 2); ?> €</p>

            <h2>Payment Information</h2>
            <form method="POST" action="">
                <label for="card_number">Card Number:</label><br />
                <input type="text" id="card_number" name="card_number" maxlength="16" required pattern="\d{16}"  /><br />

                <label for="expiry">Expiry Date (MM/YY):</label><br />
                <input type="text" id="expiry" name="expiry" maxlength="5" required pattern="\d{2}/\d{2}"  /><br />

                <label for="cvv">CVV:</label><br />
                <input type="text" id="cvv" name="cvv" maxlength="3" required pattern="\d{3}"  /><br />

                <button type="submit" id="reserve-button" disabled>Reserve</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
    // reserve button only if all fields are filled and valid
    const cardNumber = document.getElementById('card_number');
    const expiry = document.getElementById('expiry');
    const cvv = document.getElementById('cvv');
    const reserveBtn = document.getElementById('reserve-button');

    function validateInput() {
        const cardValid = /^\d{16}$/.test(cardNumber.value);
        const expiryValid = /^\d{2}\/\d{2}$/.test(expiry.value);
        const cvvValid = /^\d{3}$/.test(cvv.value);

        reserveBtn.disabled = !(cardValid && expiryValid && cvvValid);
    }

    cardNumber.addEventListener('input', validateInput);
    expiry.addEventListener('input', validateInput);
    cvv.addEventListener('input', validateInput);
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
