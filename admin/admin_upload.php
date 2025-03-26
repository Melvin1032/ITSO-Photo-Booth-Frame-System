<?php
require "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["frame"])) {
    $uploadDir = "frames/";
    $frameTmp = $_FILES["frame"]["tmp_name"];
    $frameName = uniqid() . ".png";
    $framePath = $uploadDir . $frameName;

    // Move frame to frames directory
    if (move_uploaded_file($frameTmp, $framePath)) {
        // Save frame to database
        $stmt = $pdo->prepare("INSERT INTO frames (frame_name, frame_path) VALUES (:frame_name, :frame_path)");
        $stmt->execute([
            ":frame_name" => $_FILES["frame"]["name"],
            ":frame_path" => $framePath
        ]);
        echo "Frame uploaded successfully!";
    } else {
        echo "Failed to upload frame.";
    }
}
?>

<form action="" method="post" enctype="multipart/form-data">
    <h2>Upload Frame</h2>
    <input type="file" name="frame" accept="image/png" required>
    <button type="submit">Upload Frame</button>
</form>

<a href="../user/user_dashboard.php">Go to User Page</a>
<a href="../auth/logout.php">Logout</a>
