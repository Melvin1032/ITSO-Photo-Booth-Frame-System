<?php
require "./config/db.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["photo"]) && isset($_POST["frame"])) {
        $uploadDir = "uploads/";
        $frameDir = "frames/";

        $photoTmp = $_FILES["photo"]["tmp_name"];
        $frameFile = $frameDir . $_POST["frame"];

        // Ensure frame exists
        if (!file_exists($frameFile)) {
            die("Frame not found!");
        }

        // Generate unique name
        $photoName = uniqid() . ".png";
        $photoPath = $uploadDir . $photoName;

        // Move uploaded file
        move_uploaded_file($photoTmp, $photoPath);

        // Merge photo and frame
        $outputPath = $uploadDir . "final_" . $photoName;
        mergeImages($photoPath, $frameFile, $outputPath);

        // Save to database using PDO
        try {
            $stmt = $pdo->prepare("INSERT INTO uploads (filename, frame) VALUES (:filename, :frame)");
            $stmt->execute([
                ":filename" => "final_" . $photoName,
                ":frame" => $_POST["frame"]
            ]);
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }

        // Show result
        echo "<h2>Here is your framed photo:</h2>";
        echo "<img src='$outputPath' style='max-width: 300px;'>";
        echo "<br><a href='$outputPath' download>Download Image</a>";
        echo "<br><br><a href='index.php'>Go Back</a>";
    }
}

function mergeImages($photoPath, $framePath, $outputPath) {
    $photo = imagecreatefromstring(file_get_contents($photoPath));
    $frame = imagecreatefrompng($framePath);

    // Resize frame to match photo
    list($photoWidth, $photoHeight) = getimagesize($photoPath);
    $resizedFrame = imagecreatetruecolor($photoWidth, $photoHeight);
    imagealphablending($resizedFrame, false);
    imagesavealpha($resizedFrame, true);
    imagecopyresampled($resizedFrame, $frame, 0, 0, 0, 0, $photoWidth, $photoHeight, imagesx($frame), imagesy($frame));

    // Merge images
    imagecopy($photo, $resizedFrame, 0, 0, 0, 0, $photoWidth, $photoHeight);
    imagepng($photo, $outputPath);

    // Cleanup
    imagedestroy($photo);
    imagedestroy($frame);
    imagedestroy($resizedFrame);
}
?>
