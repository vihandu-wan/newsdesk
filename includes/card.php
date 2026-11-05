<a class="card" href="/newsdesk/articles/view.php?id=<?= (int)$article['id'] ?>">
    <div class="thumb placeholder">Photo</div>
    <h3><?= htmlspecialchars($article['title']) ?></h3>
    <p><?= htmlspecialchars(summary($article['content'])) ?></p>
    <div class="meta">
        <span class="tag"><?= htmlspecialchars($article['category_name']) ?></span>
        <span><?= timeAgo($article['created_at']) ?></span>
    </div>
</a>