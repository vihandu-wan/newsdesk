<?phpsession_start();

$conn = new mysqli("localhost","root","seven","newsdesk");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SESSION['role'] != "admin") {
    die("Only Adimns can Access.");
}

if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
 
    if ($delete_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
    header("Location: users.php");
    exit();
}
$result = $conn->query("SELECT id, student_id, name, batch, faculty, degree, role FROM users");
?>
 
<h2>Manage Users</h2>
<p>Hello <?php echo htmlspecialchars($_SESSION['name']); ?> |
   <a href="../auth/logout.php">Log out</a></p>
 
<table border="1" cellpadding="6">
    <tr>
        <th>Student ID</th>
        <th>Name</th>
        <th>Batch</th>
        <th>Faculty</th>
        <th>Degree</th>
        <th>Role</th>
        <th>Action</th>
    </tr>
 
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['batch']); ?></td>
        <td><?php echo htmlspecialchars($row['faculty']); ?></td>
        <td><?php echo htmlspecialchars($row['degree']); ?></td>
        <td><?php echo htmlspecialchars($row['role']); ?></td>
        <td>
            <?php if ($row['id'] != $_SESSION['user_id']) { ?>
                <a href="users.php?delete=<?php echo $row['id']; ?>"
                   onclick="return confirm('Delete this user?')">Delete</a>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</table>
 
 