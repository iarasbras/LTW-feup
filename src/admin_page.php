<?php
// Start the session
    session_start();

    // Check if the user is logged in
    $isLoggedIn = isset($_SESSION['username']);
    $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

    if (!$isLoggedIn || !$isAdmin) {
        header('Location: login.php');
        exit();
    }

    // Establish connection to database
    try {
        $db = new PDO('sqlite:../db/cozystays.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA foreign_keys = ON;');
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        die();
    }

    // Fetch all users
    $userStmt = $db->prepare("SELECT * FROM user");
    $userStmt->execute();
    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all categories
    $categoryStmt = $db->prepare("SELECT * FROM category ORDER BY categ_name ASC");
    $categoryStmt->execute();
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <section id="manage-users">
            <div class="input-group">
                <h2>Manage Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(ucfirst($user['username'])); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['is_admin'] == 1): ?>
                                        <button onclick="setAdmin(0,'<?php echo $user['username']; ?>')" class="edit-button demote-button">Demote</button>
                                    <?php else: ?>
                                        <button onclick="setAdmin(1,'<?php echo $user['username']; ?>')" class="edit-button promote-button">Promote</button>
                                    <?php endif; ?>
                                    <button onclick="deleteUser('<?php echo $user['username']; ?>')" class="caution-button">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="manage-categories">
            <div class="input-group">
                <h2>Manage Categories</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['categ_name']); ?></td>
                                <td style="padding-left: 250px">
                                    <button onclick="deleteCategory(<?php echo $category['category_id']; ?>)" class="caution-button" >Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form id="add-category-form" onsubmit="addCategory(event)">
                    <input type="text" id="new-category-name" placeholder="New category name" required>
                    <button type="submit" class="catg-submit-button">Add Category</button>
                </form>

            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('delete_user.php?' + new URLSearchParams({ id: userId }), {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.success ? 'User deleted successfully.' : 'Failed to delete user.');
                    if (data.success) location.reload();
                })
                .catch(() => alert('Failed to delete user.'));
            }
        }

        function setAdmin(adminValue, username) {
            const action = adminValue ? 'promote' : 'demote';
            if (confirm(`Are you sure you want to ${action} this user?`)) {
                fetch('set_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_admin: adminValue, username: username })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.success ? `User ${action}d successfully.` : `Failed to ${action} user.`);
                    if (data.success) location.reload();
                })
                .catch(() => alert(`Failed to ${action} user.`));
            }
        }

        function addCategory(event) {
            event.preventDefault();
            const name = document.getElementById('new-category-name').value.trim();
            if (!name) return alert('Category name cannot be empty.');

            fetch('add_category.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.success ? 'Category added!' : 'Error adding category.');
                if (data.success) location.reload();
            })
            .catch(() => alert('Failed to add category.'));
        }

        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category?')) {
                fetch('delete_category.php?' + new URLSearchParams({ id: categoryId }), {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: categoryId })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.success ? 'Category deleted.' : 'Error deleting category.');
                    if (data.success) location.reload();
                })
                .catch(() => alert('Failed to delete category.'));
            }
        }
    </script>
</body>
</html>
