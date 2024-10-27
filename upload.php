<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include the AWS SDK
require 'vendor/autoload.php';  // Ensure this path is correct
use Aws\S3\S3Client;

// Initialize S3 Client
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => 'ap-south-1',
]);

$bucket = 'prachi2210'; // Your S3 bucket name
$userId = $_SESSION['user_id'];

// Database connection
$conn = new mysqli('database-1.cp0wuao8ky1o.ap-south-1.rds.amazonaws.com', 'admin', 'prachi11', 'image_app');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];

    // Ensure the upload directory exists
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true); // Create the uploads directory if it doesn't exist
    }

    // Upload image to S3
    try {
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => "uploads/" . basename($imageName), // Upload path in S3
            'SourceFile' => $imageTmp,
            'ACL'    => 'public-read', // Set the file to be publicly readable
        ]);
        
        $imageUrl = $result['ObjectURL']; // Get the URL of the uploaded image
        
        // Insert image URL into the database
        $conn->query("INSERT INTO images (user_id, image_path) VALUES ('$userId', '$imageUrl')");
    } catch (Exception $e) {
        echo "Error uploading file: " . $e->getMessage(); // Handle exceptions
    }
}

// Fetch user images from the database
$result = $conn->query("SELECT * FROM images WHERE user_id='$userId'");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="image" required> <!-- Added required attribute -->
    <button type="submit">Upload</button>
</form>

<h2>Your Images:</h2>
<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <img src="<?= htmlspecialchars($row['image_path']) ?>" width="200"><br> <!-- Use htmlspecialchars to prevent XSS -->
    <?php endwhile; ?>
<?php else: ?>
    <p>No images found.</p>
<?php endif; ?>

<?php
$conn->close(); // Close the database connection
?>
