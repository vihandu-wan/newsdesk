<a class="card" href="/newsdesk/articles/view.php?id=<?= (int)$article['id'] ?>">
    
<?php if (!empty($article['image_url'])): ?>
    <img class="thumb" src="<?= htmlspecialchars(imageSrc($article['image_url'])) ?>" alt="">
<?php else: ?>
    <div class="thumb placeholder">Photo</div>
<?php endif; ?>

    <h3><?= htmlspecialchars($article['title']) ?></h3>
    <p><?= htmlspecialchars(summary($article['content'])) ?></p>
    <div class="meta">
        <span class="tag"><?= htmlspecialchars($article['category_name']) ?></span>
        <span><?= timeAgo($article['created_at']) ?></span>
    </div>
</a>