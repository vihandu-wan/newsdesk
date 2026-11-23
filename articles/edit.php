<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION["user_id"])) {
    die("Please log in first.");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$user_id = (int)$_SESSION["user_id"];
$is_admin = isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);

if ($id <= 0) {
    die("Please provide a valid article ID. Example: edit.php?id=1");
}

// An author can edit their own article. An admin can edit any article.
if ($is_admin) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ? AND author_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();
$stmt->close();

if (!$article) {
    die("Article not found or you are not authorized to edit it.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update"])) {
    if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"] ?? "")) {
        die("Invalid request.");
    }

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0);
    $status = $_POST["status"] ?? "draft";
    $image_name = $article["image"];

    if ($title === "" || $content === "" || $category_id <= 0) {
        $message = "Please complete all required fields.";
    } elseif (!in_array($status, ["draft", "published"], true)) {
        $message = "Invalid article status.";
    } else {
        $check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $check->bind_param("i", $category_id);
        $check->execute();
        $category_exists = $check->get_result()->num_rows > 0;
        $check->close();

        if (!$category_exists) {
            $message = "Please select a valid category.";
        } else {
            // Upload a replacement image only if a new image was selected.
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

                        $new_image = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_image)) {
                            if (!empty($image_name) && is_file($upload_dir . $image_name)) {
                                unlink($upload_dir . $image_name);
                            }
                            $image_name = $new_image;
                        } else {
                            $message = "Could not save the uploaded image.";
                        }
                    }
                }
            }

            if ($message === "") {
                if ($is_admin) {
                    $sql = "UPDATE articles
                            SET title=?, content=?, category_id=?, image=?, status=?
                            WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssissi", $title, $content, $category_id, $image_name, $status, $id);
                } else {
                    $sql = "UPDATE articles
                            SET title=?, content=?, category_id=?, image=?, status=?
                            WHERE id=? AND author_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssissii", $title, $content, $category_id, $image_name, $status, $id, $user_id);
                }

                if ($stmt->execute()) {
                    $message = "Article updated successfully!";
                    $article["title"] = $title;
                    $article["content"] = $content;
                    $article["category_id"] = $category_id;
                    $article["image"] = $image_name;
                    $article["status"] = $status;
                } else {
                    $message = "Error updating article: " . $stmt->error;
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
<head><title>Edit Article</title></head>
<body>
<h2>Edit Article</h2>
<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo (int)$article["id"]; ?>">
    <input type="hidden" name="csrf_token"
           value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

    <label>Article title:</label><br>
    <input type="text" name="title"
           value="<?php echo htmlspecialchars($article["title"]); ?>" required><br><br>

    <label>Article content:</label><br>
    <textarea name="content" rows="8" required><?php
        echo htmlspecialchars($article["content"]);
    ?></textarea><br><br>

    <label>Category:</label><br>
    <select name="category_id" required>
        <?php while ($category = $categories->fetch_assoc()) { ?>
            <option value="<?php echo (int)$category["id"]; ?>"
                <?php echo ((int)$category["id"] === (int)$article["category_id"]) ? "selected" : ""; ?>>
                <?php echo htmlspecialchars($category["category_name"]); ?>
            </option>
        <?php } ?>
    </select><br><br>

    <label>Replace image (optional):</label><br>
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp"><br><br>

    <label>Status:</label><br>
    <select name="status">
        <option value="draft" <?php echo $article["status"] === "draft" ? "selected" : ""; ?>>Draft</option>
        <option value="published" <?php echo $article["status"] === "published" ? "selected" : ""; ?>>Published</option>
    </select><br><br>

    <button type="submit" name="update">Update Article</button>
</form>
</body>
</html>
