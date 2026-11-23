<?php
require_once __DIR__ . '/../includes/init.php';

$items = $pdo->query(
    "SELECT an.*, u.name AS author_name
     FROM announcements an LEFT JOIN users u ON u.id = an.author_id
     WHERE an.status = 'published'
     ORDER BY an.is_pinned DESC, an.published_at DESC"
)->fetchAll();

$pageTitle = 'Announcements | NSBM NewsDesk';
$activeNav = 'Announcements';
include __DIR__ . '/../includes/header.php';
?>
<main>
    <h1>Announcements</h1>
    <p class="hint">Official notices from the university, faculties and clubs.</p>

    <?php if (!$items): ?>
        <div class="empty">No announcements at the moment.</div>
    <?php endif; ?>

    <div class="announce-list">
        <?php foreach ($items as $an): ?>
            <div class="announce <?= $an['is_pinned'] ? 'pinned' : '' ?>" id="a<?= (int) $an['id'] ?>">
                <?php if ($an['is_pinned']): ?><span class="pill">📌 Pinned</span><?php endif; ?>
                <h2><?= e($an['title']) ?></h2>
                <div class="meta">
                    <span><?= e(date('M j, Y', strtotime($an['published_at'] ?? $an['created_at']))) ?></span>
                    <span>· <?= e($an['author_name'] ?? 'NewsDesk') ?></span>
                </div>
                <?php if ($an['media_url']): ?>
                    <img class="photo" src="<?= e(imageSrc($an['media_url'])) ?>" alt="">
                <?php endif; ?>
                <p><?= nl2br(e($an['content'])) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
