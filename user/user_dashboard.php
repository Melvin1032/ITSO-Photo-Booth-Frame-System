<?php
session_start();
require "../config/db.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch available frames
$stmt = $pdo->query("SELECT * FROM frames ORDER BY created_at DESC");
$frames = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Upload Your Photo</h2>
<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="photo" accept="image/*" required>

    <h3>Select a Frame</h3>
    <?php foreach ($frames as $frame): ?>
        <label class="frame-option">
            <input type="radio" name="frame_id" value="<?= $frame['id'] ?>" required>
            <img src="../admin/<?= $frame['frame_path'] ?>" width="300px">
        </label>
    <?php endforeach; ?>

    <button type="submit">Upload & Apply Frame</button>
</form>

<a href="gallery.php">View Gallery</a> | <a href="../auth/logout.php">Logout</a>



