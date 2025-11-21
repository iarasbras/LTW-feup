<?php
    // Start the session
    session_start();

    // Establish connection to database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("DB Connection failed: " . $e->getMessage());
    }

    // Handle filters
    $query = "SELECT ad.*, category.categ_name FROM ad
            LEFT JOIN category ON ad.category = category.category_id
            WHERE 1=1";
    $params = [];

    //Handle search
    if (!empty($_GET['search'])) {
        $query .= " AND ad.title LIKE :search";
        $params[':search'] = '%' . $_GET['search'] . '%';
    }

    if (!empty($_GET['category'])) {
        $query .= " AND ad.category = :category";
        $params[':category'] = $_GET['category'];
    }

    if (!empty($_GET['location'])) {
        $query .= " AND ad.location = :location";
        $params[':location'] = $_GET['location'];
    }

    $query .= " ORDER BY ad.ad_id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all categories
    $categories = $db->query("SELECT * FROM category")->fetchAll(PDO::FETCH_ASSOC);

    // Location list
    $locations = [
        "Aveiro", "Beja", "Braga", "Bragança", "Castelo Branco", "Coimbra",
        "Évora", "Faro", "Guarda", "Leiria", "Lisboa", "Portalegre", "Porto",
        "Santarém", "Setúbal", "Viana do Castelo", "Vila Real", "Viseu"
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cozystays - Home</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <h1 style="text-align:center;">All Listings</h1>

    <!-- Filter Form -->
    <form method="GET" action="" class="filter-form" style="display: flex; gap: 10px; padding: 10px; justify-content: center; align-items: center; flex-wrap: wrap;">
        <input type="text" name="search" placeholder="Search ads...  🔍︎" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

        <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['category_id']); ?>"
                    <?php if (isset($_GET['category']) && $_GET['category'] == $category['category_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($category['categ_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="location">
            <option value="">All Locations</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc; ?>" <?php if (isset($_GET['location']) && $_GET['location'] == $loc) echo 'selected'; ?>>
                    <?php echo $loc; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search</button>
    </form>

    <!-- Ads List -->
    <div class="ad-container">
        <?php if (empty($ads)): ?>
            <p>No ads found.</p>
        <?php else: ?>
            <?php foreach ($ads as $ad): ?>
                <a class="ad-card" href="ad_page.php?ad_id=<?php echo urlencode($ad['ad_id']); ?>">
                    <img src="<?php echo htmlspecialchars($ad['image_url']); ?>" alt="Ad Image">
                    <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                    <p><?php echo htmlspecialchars($ad['small_desc']); ?></p>
                    <p><strong><?php echo number_format($ad['price'], 2); ?></strong> €</p>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
