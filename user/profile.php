<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$uid = current_user_id();
$faculties = ['Faculty of Computing', 'Faculty of Business', 'Faculty of Engineering', 'Faculty of Science', 'Administration', 'Other'];

$load = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$load->execute([$uid]);
$user = $load->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name    = trim($_POST['name'] ?? '');
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $contact = trim($_POST['contact'] ?? '');
        $faculty = $_POST['faculty'] ?? '';

        if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
            $errors[] = 'Name must be 3–100 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email.';
        }
        if ($contact !== '' && !preg_match('/^\+?[0-9]{9,15}$/', $contact)) {
            $errors[] = 'Contact number should be 9–15 digits.';
        }
        if (!in_array($faculty, $faculties, true)) {
            $errors[] = 'Please choose a faculty.';
        }
        $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $dup->execute([$email, $uid]);
        if ($dup->fetch()) {
            $errors[] = 'That email is already used by another account.';
        }

        if (!$errors) {
            $pdo->prepare('UPDATE users SET name = ?, email = ?, contact = ?, faculty = ? WHERE id = ?')
                ->execute([$name, $email, $contact ?: null, $faculty, $uid]);
            flash('success', 'Profile updated.');
            redirect('user/profile.php');
        }
    }

    if ($action === 'password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'Your current password is incorrect.';
        } elseif (strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
            $errors[] = 'New password must be at least 8 characters with letters and numbers.';
        } elseif ($new !== $confirm) {
            $errors[] = 'New passwords do not match.';
        } else {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
            flash('success', 'Password changed.');
            redirect('user/profile.php');
        }
    }

    if ($action === 'delete') {
        if ($user['role'] === 'admin') {
            $errors[] = 'Admin accounts cannot be deleted here.';
        } elseif (!password_verify($_POST['delete_password'] ?? '', $user['password_hash'])) {
            $errors[] = 'Password incorrect – account not deleted.';
        } else {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
            $_SESSION = [];
            session_regenerate_id(true);
            flash('success', 'Your account has been deleted.');
            redirect('index.php');
        }
    }
}

$pageTitle = 'My profile | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main class="narrow">
    <h1>My profile</h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error inline"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="panel">
        <h2>Account details</h2>
        <p class="hint">Student ID: <strong><?= e($user['student_id']) ?></strong> · Member since <?= e(date('M Y', strtotime($user['created_at']))) ?></p>
        <form method="post" class="form" data-validate novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="profile">
            <div class="form-grid">
                <div><label for="name">Full name *</label>
                    <input id="name" name="name" required minlength="3" maxlength="100" value="<?= e($_POST['name'] ?? $user['name']) ?>"></div>
                <div><label for="email">Email *</label>
                    <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? $user['email']) ?>"></div>
                <div><label for="contact">Contact number</label>
                    <input id="contact" name="contact" pattern="\+?[0-9]{9,15}" data-msg="9–15 digits" value="<?= e($_POST['contact'] ?? $user['contact']) ?>"></div>
                <div><label for="faculty">Faculty *</label>
                    <select id="faculty" name="faculty" required>
                        <?php foreach ($faculties as $f): ?>
                            <option <?= ($_POST['faculty'] ?? $user['faculty']) === $f ? 'selected' : '' ?>><?= e($f) ?></option>
                        <?php endforeach; ?>
                    </select></div>
            </div>
            <button class="btn btn-primary" style="margin-top:14px">Save changes</button>
        </form>
    </div>

    <div class="panel">
        <h2>Change password</h2>
        <form method="post" class="form" data-validate novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password">
            <label for="current_password">Current password *</label>
            <input type="password" id="current_password" name="current_password" required>
            <div class="form-grid">
                <div><label for="new_password">New password *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8"
                           pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}" data-msg="At least 8 characters with letters and numbers"></div>
                <div><label for="confirm_password">Confirm new password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required data-match="new_password"></div>
            </div>
            <button class="btn btn-primary" style="margin-top:14px">Update password</button>
        </form>
    </div>

    <?php if ($user['role'] !== 'admin'): ?>
        <div class="panel danger-zone">
            <h2>Delete account</h2>
            <p class="hint">This permanently removes your account, comments, bookmarks and submissions.</p>
            <form method="post" class="form" data-confirm="Delete your account permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <label for="delete_password">Enter your password to confirm</label>
                <input type="password" id="delete_password" name="delete_password" required>
                <button class="btn btn-danger" style="margin-top:12px">Delete my account</button>
            </form>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
