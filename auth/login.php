
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "newsdesk_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id = trim($_POST['student_id']);
    $password = $_POST['password'];

    $sql = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
    $sql->bind_param("s", $student_id);
    $sql->execute();

    $result = $sql->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {

        $message = "Invalid student ID. Please check your ID.";

    } 
    elseif (!password_verify($password, $user['password'])) {

        $message = "Invalid password. Please try again.";

    } 
    else {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['faculty'] = $user['faculty'];
        $_SESSION['batch'] = $user['batch'];
        $_SESSION['degree'] = $user['degree'];

        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }

        exit();
    }

    $sql->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsdesk - Login</title>
</head>

<body>

    <h2>Log in</h2>

    <form method="POST" action="">

        <label for="student_id">Student ID:</label>
        <input type="text" name="student_id" id="student_id" required>

        <br><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <br><br>
        
        <?php if (!empty($message)): ?>
            <p style="color: red;">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <input type="submit" value="Login">

    </form>


</body>
</html>
