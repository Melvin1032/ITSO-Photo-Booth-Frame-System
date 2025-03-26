<?php
require "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photo"]) && isset($_POST["frame_id"])) {
    $uploadDir = "../admin/uploads/";

    // Ensure upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $photoTmp = $_FILES["photo"]["tmp_name"];
    $photoName = uniqid() . ".png";
    $photoPath = $uploadDir . $photoName;

    // Get selected frame path from the database
    $stmt = $pdo->prepare("SELECT frame_path FROM frames WHERE id = :frame_id");
    $stmt->execute([":frame_id" => $_POST["frame_id"]]);
    $frame = $stmt->fetch();

    if (!$frame) {
        die("Error: Invalid frame selection!");
    }

    $framePath = "../admin/" . $frame["frame_path"]; // Corrected frame path

    if (!file_exists($framePath)) {
        die("Error: Frame file not found at $framePath");
    }

    // Move uploaded file and validate it
    if (!move_uploaded_file($photoTmp, $photoPath)) {
        die("Error: Failed to move uploaded file.");
    }

    if (!getimagesize($photoPath)) {
        unlink($photoPath);
        die("Error: Invalid image file.");
    }

    // Merge photo and frame
    $outputPath = $uploadDir . "final_" . $photoName;
    if (!mergeImages($photoPath, $framePath, $outputPath)) {
        die("Error: Image merging failed.");
    }

    // Save to database
    $stmt = $pdo->prepare("INSERT INTO uploads (filename, frame_id) VALUES (:filename, :frame_id)");
    $stmt->execute([
        ":filename" => "final_" . $photoName,
        ":frame_id" => $_POST["frame_id"]
    ]);

    // Show result
    echo "<h2>Your Framed Photo:</h2>";
    echo "<img src='$outputPath' style='max-width: 700px;'>";
    echo "<br><a href='$outputPath' download>Download Image</a>";
    echo "<br><br><a href='../user/user_dashboard.php'>Go Back</a>";
}

// Function to merge images safely
function mergeImages($photoPath, $framePath, $outputPath) {
    $photo = imagecreatefromstring(file_get_contents($photoPath));
    if (!$photo) return false;

    $frame = imagecreatefrompng($framePath);
    if (!$frame) return false;

    list($photoWidth, $photoHeight) = getimagesize($photoPath);
    list($frameWidth, $frameHeight) = getimagesize($framePath);

    // Resize frame to match photo
    $resizedFrame = imagecreatetruecolor($photoWidth, $photoHeight);
    imagealphablending($resizedFrame, false);
    imagesavealpha($resizedFrame, true);
    imagecopyresampled($resizedFrame, $frame, 0, 0, 0, 0, $photoWidth, $photoHeight, $frameWidth, $frameHeight);

    // Merge frame onto photo
    imagecopy($photo, $resizedFrame, 0, 0, 0, 0, $photoWidth, $photoHeight);
    
    // Save final image
    imagepng($photo, $outputPath);

    // Cleanup
    imagedestroy($photo);
    imagedestroy($frame);
    imagedestroy($resizedFrame);

    return true;
}
?>
