<?php

session_start();
require_once __DIR__ . '/../db.php';

function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

$articleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$articleId) {
    http_response_code(404);
    die('Article not found.');
}


$stmt = $pdo->prepare(
    "SELECT a.id, a.title, a.content, a.image_url, a.published_at,
            c.name AS category_name,
            u.name AS author_name
     FROM articles a
     JOIN categories c ON c.id = a.category_id
     LEFT JOIN users u ON u.id = a.author_id
     WHERE a.id = :id AND a.status = 'published'"
);
$stmt->execute([':id' => $articleId]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    die('Article not found.');
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentUserId = (int) ($_SESSION['user_id'] ?? 0);
$isAdmin       = ($_SESSION['role'] ?? '') === 'admin';

$stmt = $pdo->prepare(
    "SELECT c.id, c.user_id, c.content, c.status, c.commented_at, u.name
     FROM comments c
     JOIN users u ON u.id = c.user_id
     WHERE c.article_id = :article_id
       AND (c.status = 'approved' OR (c.status = 'pending' AND c.user_id = :current_user))
     ORDER BY c.commented_at ASC"
);
$stmt->execute([
    ':article_id'   => $articleId,
    ':current_user' => $currentUserId,
]);
$comments = $stmt->fetchAll();

$flash = $_SESSION['comment_flash'] ?? null;
unset($_SESSION['comment_flash']);
?>

<html>
<html lang="en">
   <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($article['title']) ?></title>
    <style>
        body { font-family: Arial, sans-serif;
         background: #f4f6f8; 
         margin: 0; 
         padding: 20px; 
         color: #222; }


        .page { max-width: 800px; 
        margin: 0 auto;
         background: #fff; 
         padding: 25px; 
         border-radius: 6px; }


        .meta { color: #666; 
        font-size: 14px; }


        .article-image { max-width: 100%; 
        margin: 15px 0; }


        .article-body { line-height: 1.6; }


        .comments { margin-top: 40px; }


        .comment-form textarea { width: 100%;
         padding: 8px;
          box-sizing: border-box; }


        .comment-form button { margin-top: 6px; 
        padding: 6px 14px; }


        .comment { border-top: 1px solid #ddd;
         padding: 10px 0; }


        .comment-meta { font-size: 13px;
         color: #666; }


        .comment-meta span { margin-left: 8px; }


        .comment-text { margin: 6px 0;
         word-wrap: break-word; }


        .comment-flash { padding: 8px;
         border-radius: 4px; }


        .comment-flash.success { background: #e6f4ea; }


        .comment-flash.error { background: #fdecea; }
    </style>
</head>
<body>
<div class="page">

   
    <h1><?= e($article['title']) ?></h1>

    <p class="meta">
        <?= e($article['category_name']) ?>
        &middot; By <?= e($article['author_name'] ?? 'Unknown') ?>
        <?php if ($article['published_at']): ?>
            &middot; <?= e(date('M j, Y', strtotime($article['published_at']))) ?>
        <?php endif; ?>
     </p>

    <?php if (!empty($article['image_url'])): ?>
        <img class="article-image" src="<?= e($article['image_url']) ?>" alt="">
    <?php endif; ?>

    <div class="article-body">
        <?= nl2br(e($article['content'])) ?>
       </div>

   
    <section id="comments" class="comments">
        <h3>Comments (<?= count($comments) ?>)</h3>

        <?php if ($flash): ?>
            <p class="comment-flash <?= e($flash['type']) ?>"><?= e($flash['text']) ?></p>
        <?php endif; ?>

        <?php if ($currentUserId): ?>

            <form method="post" action="../comments/add.php" class="comment-form">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="article_id" value="<?= (int) $articleId ?>">
                <textarea name="content" rows="4" maxlength="1000" required
                          placeholder="Write a comment..."></textarea>
                <button type="submit">Post comment</button>
             </form>

        <?php else: ?>
            <p><a href="../login.php">Log in</a> to leave a comment.</p>
        <?php endif; ?>

        <?php if (empty($comments)): ?>
            <p>No comments yet. Be the first to comment!</p>
        <?php endif; ?>

        <?php foreach ($comments as $c): ?>
            <div class="comment">
                <div class="comment-meta">
                    <strong><?= e($c['name']) ?></strong>
                    <span><?= e(date('M j, Y g:i A', strtotime($c['commented_at']))) ?></span>
                    <?php if ($c['status'] === 'pending'): ?>
                        <em>(waiting for approval)</em>
                    <?php endif; ?>
                </div>

<p class="comment-text"><?= nl2br(e($c['content'])) ?></p>

                <?php if ($isAdmin || (int) $c['user_id'] === $currentUserId): ?>
                    <form method="post" action="../comments/delete.php"
                          onsubmit="return confirm('Delete this comment?');">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="comment_id" value="<?= (int) $c['id'] ?>">
                         <button type="submit">Delete</button>
                      </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
      </section>

</div>
   </body>



 </html>
