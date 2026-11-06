
<?php
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

function newsdesk_escape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    http_response_code(404);
    die('Article not found.');
}

$stmt = $pdo->prepare("
    SELECT
        articles.id,
        articles.title,
        articles.content,
        articles.image_url,
        articles.created_at,
        categories.name AS category_name,
        users.name AS author_name
    FROM articles
    INNER JOIN categories
        ON articles.category_id = categories.id
    LEFT JOIN users
        ON articles.author_id = users.id
    WHERE articles.id = ?
      AND articles.status = 'published'
");

$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    http_response_code(404);
    die('Sorry, that article could not be found.');
}

$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $pdo->prepare("
    SELECT
        c.id,
        c.user_id,
        c.content,
        c.status,
        c.commented_at,
        u.name
    FROM comments c
    INNER JOIN users u
        ON u.id = c.user_id
    WHERE c.article_id = ?
      AND (
          c.status = 'approved'
          OR (
              c.status = 'pending'
              AND c.user_id = ?
          )
      )
    ORDER BY c.commented_at ASC
");

$stmt->execute([$id, $currentUserId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = $_SESSION['comment_flash'] ?? null;
unset($_SESSION['comment_flash']);

$pageTitle = $article['title'] . ' | NSBM NewsDesk';
$activeNav = $article['category_name'];

include __DIR__ . '/../includes/header.php';
?>

<main>

    <div class="article">

        <a class="back" href="/newsdesk/index.php">
            &larr; Back to news
        </a>

        <!-- Article information -->
        <div class="meta">

            <a class="tag"
               href="/newsdesk/categories.php?name=<?= urlencode($article['category_name']) ?>">

                <?= newsdesk_escape($article['category_name']) ?>

            </a>

            <span>
                <?= date('j F Y', strtotime($article['created_at'])) ?>
            </span>

            <?php if (!empty($article['author_name'])): ?>

                <span>
                    · By <?= newsdesk_escape($article['author_name']) ?>
                </span>

            <?php endif; ?>

        </div>

        <h1><?= newsdesk_escape($article['title']) ?></h1>

        <?php if (!empty($article['image_url'])): ?>

            <img
                class="photo"
                src="<?= newsdesk_escape(imageSrc($article['image_url'])) ?>"
                alt=""
            >

        <?php endif; ?>

        <div class="article-body">

            <?= nl2br(newsdesk_escape($article['content'])) ?>

        </div>


        <section id="comments" class="comments">

            <h2>Comments (<?= count($comments) ?>)</h2>

            <?php if ($flash): ?>

                <p class="comment-flash <?= newsdesk_escape($flash['type']) ?>">
                    <?= newsdesk_escape($flash['text']) ?>
                </p>

            <?php endif; ?>


            <?php if ($currentUserId): ?>

                <form
                    method="POST"
                    action="../comments/add.php"
                    class="comment-form"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= newsdesk_escape($_SESSION['csrf_token']) ?>"
                    >

                    <input
                        type="hidden"
                        name="article_id"
                        value="<?= (int)$id ?>"
                    >

                    <textarea
                        name="content"
                        rows="4"
                        maxlength="1000"
                        required
                        placeholder="Write a comment..."
                    ></textarea>

                    <button type="submit">
                        Post comment
                    </button>

                </form>

            <?php else: ?>

                <p>
                    <a href="../auth/login.php">Log in</a>
                    to leave a comment.
                </p>

            <?php endif; ?>


            <?php if (empty($comments)): ?>

                <p>No comments yet. Be the first to comment!</p>

            <?php endif; ?>


            <?php foreach ($comments as $comment): ?>

                <div class="comment">

                    <div class="comment-meta">

                        <strong>
                            <?= newsdesk_escape($comment['name']) ?>
                        </strong>

                        <span>
                            <?= date(
                                'M j, Y g:i A',
                                strtotime($comment['commented_at'])
                            ) ?>
                        </span>

                        <?php if ($comment['status'] === 'pending'): ?>

                            <em>(Waiting for approval)</em>

                        <?php endif; ?>

                    </div>


                    <p class="comment-text">
                        <?= nl2br(newsdesk_escape($comment['content'])) ?>
                    </p>


                    <?php if (
                        $isAdmin ||
                        (int)$comment['user_id'] === $currentUserId
                    ): ?>

                        <form
                            method="POST"
                            action="../comments/delete.php"
                            onsubmit="return confirm('Delete this comment?');"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= newsdesk_escape($_SESSION['csrf_token']) ?>"
                            >

                            <input
                                type="hidden"
                                name="comment_id"
                                value="<?= (int)$comment['id'] ?>"
                            >

                            <button type="submit">
                                Delete
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </section>

    </div>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
