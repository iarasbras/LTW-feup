<?php
session_start();

// Check user only allow hosts
if (!isset($_SESSION['username']) || empty($_SESSION['is_host'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

// Connect to database
try {
    $db = new PDO('sqlite:../db/cozystays.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

$message = "";

// Fetch categories from the database 
$categories = [];
try {
    $stmt = $db->query("SELECT category_id, categ_name FROM category ORDER BY categ_name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Failed to load categories: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $small_desc = trim($_POST['small_desc']);
    $price = intval($_POST['price']);
    $location = $_POST['location'] ?? null;
    $category = !empty($_POST['category']) ? intval($_POST['category']) : null;
    $image_url = null;

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../populate/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            $message = "Only JPG, PNG, and GIF files are allowed.";
        } else {
            $newFileName = uniqid('img_', true) . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $image_url = $destPath;
            } else {
                $message = "Error uploading image.";
            }
        }
    } else {
        $message = "Please upload an image.";
    }

    // Validate required fields
    if (empty($title) || empty($description) || empty($price) || empty($image_url)) {
        $message = "Please fill in all required fields.";
    } elseif (empty($message)) {
        try {
            $stmt = $db->prepare("INSERT INTO ad (title, description, price, image_url, seller, small_desc, category, location)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $price, $image_url, $username, $small_desc, $category, $location]);
            $message = "Ad created successfully!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Ad - CozyStays</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <main>
        <section class="form-section">
            <form id="upload-form" method="POST" enctype="multipart/form-data">
                <div class="form-inner">  
                    <h2 class="form-title">Post a New Listing</h2>

                    <div class="form-group">
                        <label for="title">Title<span class="required">*</span></label>
                        <input type="text" id="title" name="title" required maxlength="100"
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">Upload Image<span class="required">*</span></label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>

                    <div class="form-group">
                        <label for="small_desc">Short Description (100 chars max)</label>
                        <input type="text" id="small_desc" name="small_desc" maxlength="100"
                               value="<?php echo isset($_POST['small_desc']) ? htmlspecialchars($_POST['small_desc']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Full Description<span class="required">*</span></label>
                        <textarea id="description" name="description" required maxlength="512" rows="10"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Price per night (€)<span class="required">*</span></label>
                        <input type="number" id="price" name="price" min="1" step="1" required
                               value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Choose...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['category_id']); ?>"
                                    <?php echo (isset($_POST['category']) && $_POST['category'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['categ_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <select id="location" name="location" required>
                            <option value="">Choose...</option>
                            <?php 
                            $locations = ["Aveiro", "Beja", "Braga", "Bragança", "Castelo Branco", "Coimbra", "Évora", "Faro", "Guarda", "Leiria", "Lisboa", "Portalegre", "Porto", "Santarém", "Setúbal", "Viana do Castelo", "Vila Real", "Viseu"];
                            foreach ($locations as $loc): ?>
                                <option value="<?php echo $loc; ?>" <?php echo (isset($_POST['location']) && $_POST['location'] == $loc) ? 'selected' : ''; ?>>
                                    <?php echo $loc; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="cancel-button" onclick="window.location.href='main_page.php'">Cancel</button>
                        <button type="submit" class="submit-button">Publish Ad</button>
                    </div>

                    <?php if (!empty($message)): ?>
                        <div class="form-message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>


