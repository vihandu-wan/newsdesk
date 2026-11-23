<?php
session_start();
require_once "../db.php";


if (!isset($_SESSION["user_id"])) {
    die("Please log in first.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
        die("Invalid request.");
    }

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0);
    $status = $_POST["status"] ?? "draft";
    $author_id = (int)$_SESSION["user_id"];
    $image_name = null;

    if ($title === "" || $content === "" || $category_id <= 0) {
        $message = "Please complete all required fields.";
    } elseif (!in_array($status, ["draft", "published"], true)) {
        $message = "Invalid article status.";
    } else {
        // Check that the selected category exists.
        $check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $check->bind_param("i", $category_id);
        $check->execute();
        $category_result = $check->get_result();
        $category_exists = $category_result->num_rows > 0;
        $check->close();

        if (!$category_exists) {
            $message = "Please select a valid category.";
        } else {
            // Optional image upload.
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
                    $message = "Image upload failed.";
                } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                    $message = "Image must be 2 MB or smaller.";
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($_FILES["image"]["tmp_name"]);
                    $allowed = [
                        "image/jpeg" => "jpg",
                        "image/png" => "png",
                        "image/webp" => "webp"
                    ];

                    if (!isset($allowed[$mime])) {
                        $message = "Only JPG, PNG, and WEBP images are allowed.";
                    } else {
                        $upload_dir = "../uploads/";
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }

                        $image_name = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
                        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $image_name)) {
                            $message = "Could not save the uploaded image.";
                            $image_name = null;
                        }
                    }
                }
            }

            if ($message === "") {
                $sql = "INSERT INTO articles
                        (title, content, category_id, author_id, image, status)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiiss", $title, $content, $category_id, $author_id, $image_name, $status);

                if ($stmt->execute()) {
                    $message = "Article created successfully!";
                } else {
                    $message = "Error creating article: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$categories = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name");
?>
<!DOCTYPE html>
<html>
<head><title>Create Article</title></head>
<body>
<h2>Create New Article</h2>
<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token"
           value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

    <label>Article title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Article content:</label><br>
    <textarea name="content" rows="8" required></textarea><br><br>

    <label>Category:</label><br>
    <select name="category_id" required>
        <option value="">Select category</option>
        <?php while ($category = $categories->fetch_assoc()) { ?>
            <option value="<?php echo (int)$category["id"]; ?>">
                <?php echo htmlspecialchars($category["category_name"]); ?>
            </option>
        <?php } ?>
    </select><br><br>

    <label>Image (optional):</label><br>
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"><br><br>

    <label>Status:</label><br>
    <select name="status">
        <option value="draft">Draft</option>
        <option value="published">Published</option>
    </select><br><br>

    <button type="submit" name="submit">Create Article</button>
</form>
</body>
</html>
