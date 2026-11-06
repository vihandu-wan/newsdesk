<?php

session_start();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


$user_id = $_SESSION['user_id'];


$sql = "SELECT article_submissions.*, categories.name AS category_name 
        FROM article_submissions 
        JOIN categories ON article_submissions.category_id = categories.id 
        WHERE article_submissions.user_id = '$user_id'";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Submissions</title>
</head>
<body>
    <h2>My Submission History</h2>
    <a href="create.php">Submit New Article</a><br><br>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Review Note</th>
        </tr>

        <?php
   
        if ($result->num_rows > 0) {
        
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['title'] . "</td>";
                echo "<td>" . $row['category_name'] . "</td>";
                echo "<td><b>" . ucfirst($row['status']) . "</b></td>";
                

                $note = ($row['review_note'] != "") ? $row['review_note'] : 'None';
                echo "<td>" . $note . "</td>";
                
                echo "</tr>";
            }
        } else {

            echo "<tr><td colspan='4'>No submissions found.</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php 

$conn->close(); 
?>