<?php
require_once __DIR__ . '/../includes/init.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Please enter your email / student ID and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? OR student_id = ? LIMIT 1');
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Incorrect email / student ID or password.';
        } elseif (!$user['is_active']) {
            $error = 'This account has been deactivated. Please contact the admin.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']    = (int) $user['id'];
            $_SESSION['name']       = $user['name'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['student_id'] = $user['student_id'];

            flash('success', 'Welcome back, ' . $user['name'] . '!');

            $next = $_SESSION['after_login'] ?? '';
            unset($_SESSION['after_login']);
            if ($next !== '' && $next[0] === '/' && substr($next, 0, 2) !== '//') {
                header('Location: ' . $next);
                exit;
            }
            redirect($user['role'] === 'admin' ? 'admin/index.php' : 'user/dashboard.php');
        }
    }
}

$pageTitle = 'Log in | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <div class="auth-card">
        <h1>Log in</h1>
        <p class="hint">Use your NSBM email or student ID.</p>

        <?php if ($error): ?>
            <div class="alert alert-error inline"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form" data-validate novalidate>
            <?= csrf_field() ?>

            <label for="login">Email or student ID</label>
            <input type="text" id="login" name="login" value="<?= old('login') ?>" required autofocus>

            <label for="password">Password</label>
            <div class="password-wrap">
                <input type="password" id="password" name="password" required>
                <button type="button" class="show-pass" data-show="password">Show</button>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </form>

        <p class="hint center">New to NewsDesk? <a href="<?= url('auth/register.php') ?>">Create an account</a></p>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
