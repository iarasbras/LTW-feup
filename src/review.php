<?php
    session_start();

    // Redirect if not logged in
    if (!isset($_SESSION['username'])) {
        header('Location: login.php');
        exit;
    }

    $username = $_SESSION['username'];

    // Check for ad_id in GET from profile
    if (!isset($_GET['ad_id']) || !is_numeric($_GET['ad_id'])) {
        die("Invalid Ad ID.");
    }

    $ad_id = (int)$_GET['ad_id'];

    // Connect to database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("DB connection failed: " . $e->getMessage());
    }

    $message = "";

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
        $comment = trim($_POST['comment'] ?? '');

        // make sure rating is valid
        if ($rating === null || $rating < 0 || $rating > 5) {
            $message = "Please select a rating between 0 and 5.";
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO review (ad_id, username, rating, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$ad_id, $username, $rating, $comment]);

                $message = "Thank you for your review!";
            } catch (PDOException $e) {
                $message = "Error saving review: " . $e->getMessage();
            }
        }
    }

    //fetch ad title to show on page
    try {
        $stmt = $db->prepare("SELECT title FROM ad WHERE ad_id = ?");
        $stmt->execute([$ad_id]);
        $ad = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ad) {
            die("Ad not found.");
        }
    } catch (PDOException $e) {
        die("Error fetching ad: " . $e->getMessage());
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Review Ad - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css" />
</head>
<body>

    <?php include 'header.php'; ?>

    <main>
        <section class="form-section">
            <div class="form-inner">
                <h2 class="form-title">Leave a Review for: <?php echo htmlspecialchars($ad['title']); ?></h2>

                <?php if ($message): ?>
                    <div class="form-message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="review-form">
                    <div class="form-group">
                        <label for="rating">Your rating ★<span class="required">*</span></label>
                        <select id="rating" name="rating" required>
                            <option value="">Select rating</option>
                            <?php for ($i=0; $i <=5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="comment">Comment (optional)</label>
                        <textarea id="comment" name="comment" maxlength="255" rows="5" placeholder="Write your comment here..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="cancel-button" onclick="window.history.back();">Cancel</button>
                        <button type="submit" class="submit-button">Submit Review</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
