<?php
<<<<<<< HEAD
require_once __DIR__ . '/../includes/init.php';
require_admin();

$allowed = ['pending', 'approved', 'rejected', 'hidden'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    $newStatus = $_POST['new_status'] ?? '';
    if ($commentId && in_array($newStatus, $allowed, true)) {
        $pdo->prepare('UPDATE comments SET status = ? WHERE id = ?')->execute([$newStatus, $commentId]);
        flash('success', 'Comment marked as ' . $newStatus . '.');
    }
    redirect('admin/comments.php?status=' . urlencode($_GET['status'] ?? 'pending'));
}

$filter = $_GET['status'] ?? 'pending';
if ($filter !== 'all' && !in_array($filter, $allowed, true)) {
    $filter = 'pending';
}
$q = trim($_GET['q'] ?? '');
=======
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
 }
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Access denied.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
 }

function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
 }

$allowedStatuses = ['pending', 'approved', 'rejected', 'hidden'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Invalid request.');
    }

     $commentId = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    $newStatus = $_POST['new_status'] ?? '';

    if ($commentId && in_array($newStatus, $allowedStatuses, true)) {
         $update = $pdo->prepare("UPDATE comments SET status = :status WHERE id = :id");
        $update->execute([':status' => $newStatus, ':id' => $commentId]);
        $message = 'Comment #' . $commentId . ' marked as ' . $newStatus . '.';
    }
}

if (!empty($_SESSION['comment_flash'])) {
    $message = $_SESSION['comment_flash']['text'];
    unset($_SESSION['comment_flash']);
  }

$filter = $_GET['status'] ?? 'pending';
if ($filter !== 'all' && !in_array($filter, $allowedStatuses, true)) {
    $filter = 'pending';
}
>>>>>>> origin/newsdesk-admin-panel

$sql = "SELECT c.id, c.content, c.status, c.commented_at,
               u.name AS user_name, u.email AS user_email,
               a.id AS article_id, a.title AS article_title
        FROM comments c
        JOIN users u    ON u.id = c.user_id
<<<<<<< HEAD
        JOIN articles a ON a.id = c.article_id
        WHERE 1 = 1";
$params = [];
if ($filter !== 'all') {
    $sql .= ' AND c.status = ?';
    $params[] = $filter;
}
if ($q !== '') {
    $sql .= ' AND (c.content LIKE ? OR u.name LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
$sql .= ' ORDER BY c.commented_at DESC LIMIT 100';
=======
        JOIN articles a ON a.id = c.article_id";

$params = [];
if ($filter !== 'all') {
    $sql .= " WHERE c.status = :status";
    $params[':status'] = $filter;
}
$sql .= " ORDER BY c.commented_at DESC LIMIT 100";
>>>>>>> origin/newsdesk-admin-panel

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comments = $stmt->fetchAll();
<<<<<<< HEAD

$pageTitle = 'Comment moderation';
$adminNav  = 'comments';
include __DIR__ . '/_header.php';
?>
<div class="page-head">
    <div class="tabs">
        <?php foreach (array_merge($allowed, ['all']) as $tab): ?>
            <a href="?status=<?= e($tab) ?>" class="<?= $tab === $filter ? 'active' : '' ?>"><?= e(ucfirst($tab)) ?></a>
        <?php endforeach; ?>
    </div>
    <form method="get" class="row">
        <input type="hidden" name="status" value="<?= e($filter) ?>">
        <input name="q" value="<?= e($q) ?>" class="filter-select" placeholder="Search comments or users">
        <button class="btn btn-sm">Search</button>
    </form>
</div>

<?php if (!$comments): ?>
    <div class="empty">No comments in this list.</div>
<?php endif; ?>

<?php foreach ($comments as $c): ?>
    <div class="panel comment-card">
        <div class="meta">
            <strong><?= e($c['user_name']) ?></strong> (<?= e($c['user_email']) ?>)
            on <a href="<?= url('articles/view.php?id=' . (int) $c['article_id']) ?>#comments" target="_blank"><?= e($c['article_title']) ?></a>
            · <?= e(date('M j, Y g:i A', strtotime($c['commented_at']))) ?>
            <?= status_badge($c['status']) ?>
        </div>
        <p class="comment-text"><?= nl2br(e($c['content'])) ?></p>
        <div class="actions">
            <?php foreach (['approved' => ['Approve', 'btn-success'], 'rejected' => ['Reject', 'btn-danger'], 'hidden' => ['Hide', '']] as $value => [$label, $cls]): ?>
                <?php if ($c['status'] !== $value): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                        <input type="hidden" name="new_status" value="<?= $value ?>">
                        <button class="btn btn-sm <?= $cls ?>"><?= $label ?></button>
                    </form>
                <?php endif; ?>
            <?php endforeach; ?>
            <form method="post" action="<?= url('comments/delete.php') ?>" data-confirm="Delete this comment permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                <input type="hidden" name="from" value="admin">
                <button class="btn btn-sm">Delete</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
<?php include __DIR__ . '/_footer.php'; ?>
=======
?>

<html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comment Moderation</title>
    <style>
        body { font-family: Arial, sans-serif;
         background: #f4f6f8;
          margin: 0;
           padding: 20px; 
           color: #222; }


        .wrap { max-width: 1000px; 
        margin: 0 auto; }


        h1 { margin-top: 0; }

        .tabs a { display: inline-block;
         padding: 6px 14px;
          margin-right: 4px;
           background: #e1e6eb;
                  color: #222;
                 text-decoration: none;
                 border-radius: 4px; }


        .tabs a.active { background: #1f4e79;
         color: #fff; }


        .notice { background: #e6f4ea;
         border: 1px solid #b7dfc3;
          padding: 10px;
           margin: 15px 0;
            border-radius: 4px; }


        .card { background: #fff;
         border: 1px solid #dde2e7;
          border-radius: 6px; 
          padding: 14px;
           margin-top: 12px; }


        .meta { font-size: 13px;
         color: #666; 
         margin-bottom: 8px; }


        .text { white-space: pre-wrap; 
        word-wrap: break-word;
         margin: 8px 0 12px; }


        .badge { padding: 2px 8px;
         border-radius: 10px; 
         font-size: 12px;
          background: #ddd; }


        .badge.pending { background: #fff3cd; }


        .badge.approved { background: #d4edda; }


        .badge.rejected { background: #f8d7da; }


        .badge.hidden { background: #e2e3e5; }


        form { display: inline; }


        button { padding: 5px 12px;
         border: none;
          border-radius: 4px; 
          cursor: pointer;
           color: #fff; }


        .approve { background: #2e7d32; }


        .reject  { background: #c62828; }


        .hide    { background: #6c757d; }


        .delete  { background: #000; }

    </style>
</head>
<body>
    <div class="wrap">
       <h1>Comment Moderation</h1>

    <div class="tabs">
        <?php foreach (array_merge($allowedStatuses, ['all']) as $tab): ?>
            <a href="?status=<?= e($tab) ?>" class="<?= $tab === $filter ? 'active' : '' ?>">
                <?= e(ucfirst($tab)) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($message !== ''): ?>
        <div class="notice"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if (empty($comments)): ?>
        <p>No comments in this list.</p>
    <?php endif; ?>

    <?php foreach ($comments as $c): ?>
        <div class="card">
            <div class="meta">
                <strong><?= e($c['user_name']) ?></strong> (<?= e($c['user_email']) ?>)
                on <a href="../articles/view.php?id=<?= (int) $c['article_id'] ?>#comments"><?= e($c['article_title']) ?></a>
                &middot; <?= e(date('M j, Y g:i A', strtotime($c['commented_at']))) ?>
                &middot; <span class="badge <?= e($c['status']) ?>"><?= e($c['status']) ?></span>
            </div>

<div class="text"><?= e($c['content']) ?></div>

            
            <?php foreach (['approved' => 'approve', 'rejected' => 'reject', 'hidden' => 'hide'] as $statusValue => $btnClass): ?>
            <?php if ($c['status'] !== $statusValue): ?>
                <form method="post" action="comments.php?status=<?= e($filter) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                        <input type="hidden" name="new_status" value="<?= e($statusValue) ?>">
                        <button type="submit" class="<?= e($btnClass) ?>">
                            <?= e(ucfirst($btnClass)) ?>
                    </button>
                    </form>
                <?php endif; ?>
            <?php endforeach; ?>

 <form method="post" action="../comments/delete.php"
                  onsubmit="return confirm('Delete this comment permanently?');">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
            <input type="hidden" name="from" value="admin">
            <button type="submit" class="delete">Delete</button>
            </form>
        </div>
    <?php endforeach; ?>
 </div>
    </body>
</html>
>>>>>>> origin/newsdesk-admin-panel
