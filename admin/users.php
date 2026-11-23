<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$me = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
    $action = $_POST['action'] ?? '';

    if ($id === $me) {
        flash('error', 'You cannot change your own account here.');
    } elseif ($action === 'toggle_active') {
        $pdo->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        flash('success', 'User status updated.');
    } elseif ($action === 'make_admin' || $action === 'make_student') {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$action === 'make_admin' ? 'admin' : 'student', $id]);
        flash('success', 'User role updated.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        flash('success', 'User deleted.');
    }
    redirect('admin/users.php?' . http_build_query(array_intersect_key($_GET, array_flip(['q', 'role']))));
}

$q    = trim($_GET['q'] ?? '');
$role = $_GET['role'] ?? '';

$sql = "SELECT u.*,
               (SELECT COUNT(*) FROM article_submissions s WHERE s.author_id = u.id) AS submissions,
               (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id) AS comments
        FROM users u WHERE 1 = 1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (u.name LIKE ? OR u.email LIKE ? OR u.student_id LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%");
}
if (in_array($role, ['student', 'admin'], true)) {
    $sql .= ' AND u.role = ?';
    $params[] = $role;
}
$sql .= ' ORDER BY u.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Users';
$adminNav  = 'users';
include __DIR__ . '/_header.php';
?>
<form class="row filters" method="get">
    <input name="q" value="<?= e($q) ?>" class="filter-select" placeholder="Name, email or student ID">
    <select name="role" class="filter-select" data-autosubmit>
        <option value="">All roles</option>
        <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Students</option>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admins</option>
    </select>
    <button class="btn btn-sm">Search</button>
    <span class="hint"><?= count($users) ?> user(s)</span>
</form>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Name</th><th>Student ID</th><th>Faculty</th><th>Role</th><th>Status</th><th>Activity</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><strong><?= e($u['name']) ?></strong><br><small class="hint"><?= e($u['email']) ?></small></td>
                <td><?= e($u['student_id']) ?></td>
                <td><?= e($u['faculty']) ?></td>
                <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : '' ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                <td><?= status_badge($u['is_active'] ? 'active' : 'inactive') ?></td>
                <td><small><?= (int) $u['submissions'] ?> submissions<br><?= (int) $u['comments'] ?> comments</small></td>
                <td><small><?= e(date('M j, Y', strtotime($u['created_at']))) ?></small></td>
                <td class="actions">
                    <?php if ((int) $u['id'] === $me): ?>
                        <span class="hint">You</span>
                    <?php else: ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="btn btn-sm" name="action" value="toggle_active"><?= $u['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                            <?php if ($u['role'] === 'student'): ?>
                                <button class="btn btn-sm" name="action" value="make_admin">Make admin</button>
                            <?php else: ?>
                                <button class="btn btn-sm" name="action" value="make_student">Make student</button>
                            <?php endif; ?>
                        </form>
                        <form method="post" data-confirm="Delete <?= e($u['name']) ?> and all their comments/submissions?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="btn btn-sm btn-danger" name="action" value="delete">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
