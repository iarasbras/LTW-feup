<?php
    // Start the session
    session_start();

    // Check if the user is logged in
    $isLoggedIn = isset($_SESSION['username']);
    $username = $_SESSION['username'] ?? '';
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
        $stmt = $db->prepare("SELECT * FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch ad and seller information
    if (isset($_GET['ad_id'])) {
        $adId = $_GET['ad_id'];
        $stmt = $db->prepare("SELECT * FROM ad WHERE ad_id = ?");
        $stmt->execute([$adId]);
        $ad = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ad) {
            $sellerStmt = $db->prepare("SELECT * FROM user WHERE username = ?");
            $sellerStmt->execute([$ad['seller']]);
            $seller = $sellerStmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo 'Ad not found';
            die();
        }
    } else {
        echo 'No ad specified';
        die();
    }

    // Determine if the logged-in user is the seller of the ad
    $isSeller = $isLoggedIn && $username === $ad['seller'];

    // Prepare statement to fetch all reviews
    $reviewStmt = $db->prepare("SELECT r.rating, r.comment, r.created_at, r.username, u.profile_img_url
                                FROM review r
                                JOIN user u ON r.username = u.username
                                WHERE r.ad_id = ?
                                ORDER BY r.created_at DESC");

    $reviewStmt->execute([$adId]);
    $reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare statement to get average rating and count of reviews for the ad
    $avgRatingStmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM review WHERE ad_id = ?");
    $avgRatingStmt->execute([$adId]);
    $avgRatingData = $avgRatingStmt->fetch(PDO::FETCH_ASSOC);
    $avgRating = $avgRatingData['avg_rating'] ? round($avgRatingData['avg_rating'], 1) : '0 ratings';

    // Get the total number of reviews or default to 0
    $reviewCount = $avgRatingData['review_count'] ?? 0;

    // Fetch booked date ranges
    $bookedStmt = $db->prepare("SELECT booked_at, until_at FROM booking WHERE ad_id = ?");
    $bookedStmt->execute([$adId]);
    $bookedRanges = $bookedStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>CozyStays - <?php echo htmlspecialchars($ad['title']); ?></title>
    <link rel="stylesheet" href="stylesheet.css" />
    <script>
        // Pass booked dates from PHP to JS
        const bookedDates = <?php
            $booked = [];
            foreach ($bookedRanges as $range) {
                $start = new DateTime($range['booked_at']);
                $end = new DateTime($range['until_at']);
                while ($start <= $end) {
                    $booked[] = $start->format('Y-m-d');
                    $start->modify('+1 day');
                }
            }
            echo json_encode($booked);
        ?>;
    </script>
</head>
<body>

    <?php include 'header.php'; ?>

    <div id="adpage">
        <div id="ad-container">
            <div id="image-ad">
                <img src="../populate/<?php echo htmlspecialchars($ad['image_url']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" />
            </div>

            <div id="booking-details-card">
                <div class="top-row">
                    <h1 class="ad-title" style="text-align: left"><?php echo htmlspecialchars(ucfirst($ad['title'])); ?></h1>
                    <div class="rating">
                        <div class="star">★</div>
                        <div class="rating-value" style="text-align: center"><?php echo is_numeric($avgRating) ? $avgRating : $avgRating; ?></div>
                    </div>
                </div>

                <div class="price-info">
                    <div class="price"><?php echo htmlspecialchars($ad['price']); ?> €</div>
                    <div>/night</div>
                </div>

                <div class="ratings-link">
                    <a href="#reviews"><?php echo $reviewCount ?> review<?php echo $reviewCount !== 1 ? 's' : ''; ?></a>
                </div>

                <!-- Booking dates -->
                <form id="booking-form" method="GET" action="payment.php">
                    <input type="hidden" name="ad_id" value="<?php echo $adId; ?>" />
                    <div class="check-in-out">
                        <div class="date-picker">
                            <label for="check-in">Check-in</label>
                            <select id="check-in" name="check_in" required>
                                <option value="" selected>Select check-in</option>
                            </select>
                        </div>
                        <div class="date-picker">
                            <label for="check-out">Check-out</label>
                            <select id="check-out" name="check_out" required>
                                <option value="" selected>Select check-out</option>
                            </select>
                        </div>
                    </div>

                    <p id="date-warning" style="color: red; display: none; margin-top: 10px;">
                        Selected range includes unavailable dates.
                    </p>

                    <?php if (!$isSeller && $isLoggedIn): ?>
                        <button type="submit" id="book-btn" class="book-button" disabled>Book</button>
                    <?php elseif ($isSeller): ?>
                        <button class="book-button" disabled>Your Listing</button>
                    <?php else: ?>
                        <button class="book-button" onclick="window.location.href='login.php'" type="button">Login to Book</button>
                    <?php endif; ?>
                </form>

                <div class="description-section">
                    <h2 class="section-title" style="text-align: left">Description</h2>
                    <p><?php echo htmlspecialchars(ucfirst($ad['description'])); ?></p>
                </div>

                <div class="host-info-inline">
                    <img class="host-avatar" src="<?php echo htmlspecialchars($seller['profile_img_url']); ?>" alt="Host Avatar">
                    <div class="host-text">
                        <h3 class="host-name">Hosted by <?php echo htmlspecialchars(ucfirst($seller['username'])); ?></h3>
                        <p class="host-bio"><?php echo htmlspecialchars($seller['bio'] ?: 'No bio available.'); ?></p>
                    
                    <?php if ($isLoggedIn && !$isSeller): ?>
                        <form method="POST" action="start_conversation.php">
                            <input type="hidden" name="receiver" value="<?php echo htmlspecialchars($ad['seller']); ?>">
                            <input type="hidden" name="ad_id" value="<?php echo htmlspecialchars($ad['ad_id']); ?>">
                            <button type="submit" class="contact-advertiser-button">Contact Advertiser</button>
                        </form>
                    <?php endif; ?>

    
                    
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="reviews-section" id="reviews">
        <h2>Reviews</h2>
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <img class="review-avatar" src="<?php echo htmlspecialchars($review['profile_img_url']); ?>" alt="User Avatar" />
                        <div class="review-user-info">
                            <strong class="review-username"><?php echo htmlspecialchars(ucfirst($review['username'])); ?></strong>
                            <small class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                        <div class="review-rating">
                            <?php
                                $fullStars = (int)$review['rating'];
                                for ($i = 0; $i < 5; $i++) {
                                    echo '<div class="star">' . ($i < $fullStars ? '★' : '☆') . '</div>';
                                }
                            ?>
                        </div>
                    </div>
                    <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; padding: 20px 0;">No reviews yet.</p>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkIn = document.getElementById('check-in');
        const checkOut = document.getElementById('check-out');
        const bookBtn = document.getElementById('book-btn');
        const warning = document.getElementById('date-warning');

        const today = new Date();
        const daysToShow = 370;

        function formatDate(d) {
            return d.toISOString().split('T')[0];
        }

        // Populate selects with the date options
        for (let i = 0; i < daysToShow; i++) {
            const date = new Date(today);
            date.setDate(date.getDate() + i);
            const dateStr = formatDate(date);

            const isBooked = bookedDates.includes(dateStr);
            const label = isBooked ? `${dateStr} ✖️ (unavailable)` : dateStr;

            const optionIn = document.createElement('option');
            optionIn.value = dateStr;
            optionIn.textContent = label;
            if (isBooked) {
                optionIn.disabled = true;
                optionIn.style.color = 'red';
            }
            checkIn.appendChild(optionIn);

            // Clone for check-out picker
            const optionOut = optionIn.cloneNode(true);
            checkOut.appendChild(optionOut);
        }

        function validateSelection() {
            const checkInVal = checkIn.value;
            const checkOutVal = checkOut.value;

            if (!checkInVal || !checkOutVal || checkInVal >= checkOutVal) {
                bookBtn.disabled = true;
                warning.style.display = 'none';
                return;
            }

            // Check if the range isnt valide
            let current = new Date(checkInVal);
            const end = new Date(checkOutVal);
            let hasConflict = false;

            while (current < end) {
                const dateStr = current.toISOString().split('T')[0];
                if (bookedDates.includes(dateStr)) {
                    hasConflict = true;
                    break;
                }
                current.setDate(current.getDate() + 1);
            }

            bookBtn.disabled = hasConflict;

            if (hasConflict) {
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        }

        checkIn.addEventListener('change', () => {
            const checkInVal = checkIn.value;

            for (let i = 1; i < checkOut.options.length; i++) {
                const option = checkOut.options[i];
                const optionDate = option.value;

                const isUnavailable = optionDate <= checkInVal || bookedDates.includes(optionDate);

                option.disabled = isUnavailable;

                // Update label text to add or remove ✖️ (unavailable)
                if (isUnavailable) {
                    option.textContent = `${optionDate} ✖️ (unavailable)`;
                    option.style.color = 'red';
                } else {
                    option.textContent = optionDate;
                    option.style.color = '';
                }
            }

            // Reset check-out if invalid
            if (checkOut.value <= checkInVal) {
                checkOut.value = '';
            }

            validateSelection();
        });

        checkOut.addEventListener('change', validateSelection);
    });
    </script>

</body>
</html>

