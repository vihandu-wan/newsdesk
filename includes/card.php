<a class="card" href="<?= url('articles/view.php?id=' . (int) $article['id']) ?>">
    <?php if (!empty($article['image_url'])): ?>
        <img class="thumb" src="<?= e(imageSrc($article['image_url'])) ?>" alt="" loading="lazy">
    <?php else: ?>
        <div class="thumb placeholder"><?= e($article['category_name']) ?></div>
    <?php endif; ?>

    <h3><?= e($article['title']) ?></h3>
    <p><?= e(summary($article['content'])) ?></p>
    <div class="meta">
        <span class="tag"><?= e($article['category_name']) ?></span>
        <span><?= timeAgo($article['published_at'] ?? $article['created_at']) ?></span>
    </div>
</a>
