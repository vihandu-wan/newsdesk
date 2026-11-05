<?php
session_start();

$conn=new mysql("localhost","root","seven","newsdesk");

$message="";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stdent_id = trim($_POST['student_id']);
    $password = $POST['password'];

    $sql=$conn->prepare("SELECT * FROM user WHERE student_id='$student_id'"); 
    $sql->blind_param("s",$student_id);
    $sql->execute();
    $result=$sql->get_result();
    $user=$result->fetch_assoc();

    if($user and password_verify($password,$user['password'])){
        $_SESSION['user_id']=$user['id'];
        $_SESSION['name']=$user['name'];
        $_SESSION['student_id']=$user['student_id'];
        $_SESSION['faculty']=$user['faculty'];
        $_SESSION['batch']=$user['batch'];
        $_SESSION['degree']=$user['degree'];

        if($user['role']=='admin'){
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        $message="Invalid student ID or password.";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Login page</title>
</head>
<body>
    <h2>Log in</h2>

    <p><?php echo $message; ?></p>
    <form method="POST" action="">
        <label>Student ID:</label>
        <input type="text" name="student_id" id="student_id" required><br><br>

        <label>Password:</label>
        <input type="password" name="password" id="password" required><br><br>

        <input type="button" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</body>
</html>