<?php
require_once __DIR__ . '/../includes/init.php';

if (is_logged_in()) {
    redirect('user/dashboard.php');
}

$faculties = [
    'Faculty of Computing',
    'Faculty of Business',
    'Faculty of Engineering',
    'Faculty of Science',
    'Other',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name       = trim($_POST['name'] ?? '');
    $student_id = strtoupper(trim($_POST['student_id'] ?? ''));
    $email      = strtolower(trim($_POST['email'] ?? ''));
    $contact    = trim($_POST['contact'] ?? '');
    $faculty    = $_POST['faculty'] ?? '';
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
        $errors['name'] = 'Name must be 3–100 characters.';
    }
    if (!preg_match('/^[A-Z0-9]{4,20}$/', $student_id)) {
        $errors['student_id'] = 'Student ID must be 4–20 letters/numbers.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($contact !== '' && !preg_match('/^\+?[0-9]{9,15}$/', $contact)) {
        $errors['contact'] = 'Contact number should be 9–15 digits.';
    }
    if (!in_array($faculty, $faculties, true)) {
        $errors['faculty'] = 'Please choose your faculty.';
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must be at least 8 characters with letters and numbers.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT email, student_id FROM users WHERE email = ? OR student_id = ?');
        $check->execute([$email, $student_id]);
        foreach ($check->fetchAll() as $row) {
            if ($row['email'] === $email) {
                $errors['email'] = 'This email is already registered.';
            }
            if ($row['student_id'] === $student_id) {
                $errors['student_id'] = 'This student ID is already registered.';
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            "INSERT INTO users (student_id, name, email, contact, faculty, password_hash, role)
             VALUES (?, ?, ?, ?, ?, ?, 'student')"
        );
        $stmt->execute([
            $student_id, $name, $email, $contact ?: null, $faculty,
            password_hash($password, PASSWORD_DEFAULT),
        ]);

        session_regenerate_id(true);
        $_SESSION['user_id']    = (int) $pdo->lastInsertId();
        $_SESSION['name']       = $name;
        $_SESSION['role']       = 'student';
        $_SESSION['student_id'] = $student_id;

        flash('success', 'Account created! Pick some categories to follow.');
        redirect('subscriptions/categories.php');
    }
}

$pageTitle = 'Register | NSBM NewsDesk';
include __DIR__ . '/../includes/header.php';

$err = function ($field) use ($errors) {
    return isset($errors[$field]) ? '<small class="field-error">' . e($errors[$field]) . '</small>' : '';
};
?>
<main>
    <div class="auth-card wide">
        <h1>Create your account</h1>
        <p class="hint">Register to comment, save articles, follow categories and submit your own news.</p>

        <form method="post" class="form" data-validate novalidate>
            <?= csrf_field() ?>
            <div class="form-grid">
                <div>
                    <label for="name">Full name *</label>
                    <input id="name" name="name" value="<?= old('name') ?>" required minlength="3" maxlength="100">
                    <?= $err('name') ?>
                </div>
                <div>
                    <label for="student_id">Student ID *</label>
                    <input id="student_id" name="student_id" value="<?= old('student_id') ?>" required
                           pattern="[A-Za-z0-9]{4,20}" data-msg="4–20 letters/numbers">
                    <?= $err('student_id') ?>
                </div>
                <div>
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
                    <?= $err('email') ?>
                </div>
                <div>
                    <label for="contact">Contact number</label>
                    <input id="contact" name="contact" value="<?= old('contact') ?>"
                           pattern="\+?[0-9]{9,15}" data-msg="9–15 digits">
                    <?= $err('contact') ?>
                </div>
                <div class="full">
                    <label for="faculty">Faculty *</label>
                    <select id="faculty" name="faculty" required>
                        <option value="">Choose your faculty</option>
                        <?php foreach ($faculties as $f): ?>
                            <option <?= ($_POST['faculty'] ?? '') === $f ? 'selected' : '' ?>><?= e($f) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= $err('faculty') ?>
                </div>
                <div>
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}" data-msg="At least 8 characters with letters and numbers">
                    <?= $err('password') ?>
                </div>
                <div>
                    <label for="confirm_password">Confirm password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required data-match="password">
                    <?= $err('confirm_password') ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create account</button>
        </form>

        <p class="hint center">Already registered? <a href="<?= url('auth/login.php') ?>">Log in</a></p>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
